<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Exception\UnsupportedException;
use Symfony\Component\HttpFoundation\Request;
use JsonSchema\Validator;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use Symfony\Component\Serializer\Encoder\JsonDecode;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestValidator
{
    /**
     * @param Request $request
     * @param array   $operationDefinition
     *
     * @throws InvalidParametersException
     * @throws UnsupportedException
     */
    public function validateRequest(Request $request, array $operationDefinition)
    {
        // This retrieves the modified parameters
        $parameters = $this->assembleParameterDataForValidation($request, $operationDefinition);

        // Validate the parameters using a schema created from the operation definition
        $validator = new Validator();
        $schema = $this->assembleRequestSchema($operationDefinition);
        $validator->check($parameters, $schema);

        if (!$validator->isValid()) {
            /**
             * TODO Better utilize $validator->getErrors() so we can assemble a more helpful vnd.error response
             * @see https://github.com/kleijnweb/swagger-bundle/issues/27
             */
            throw new InvalidParametersException(
                "Parameters incompatible with operation schema: "
                . implode(', ', $validator->getErrors()[0]),
                400
            );
        }
    }

    /**
     * @param array $operationDefinition
     *
     * @return object
     */
    private function assembleRequestSchema(array $operationDefinition)
    {
        if (!isset($operationDefinition['parameters'])) {
            return new \stdClass;
        }

        $schema = [
            'type'       => 'object',
            'properties' => [],
            'required'   => []
        ];

        foreach ($operationDefinition['parameters'] as $paramDefinition) {
            $propertySchema = isset($paramDefinition['schema'])
                ? $paramDefinition['schema']
                : $paramDefinition;

            if (isset($paramDefinition['required']) && $paramDefinition['required']) {
                $schema['required'][] = $paramDefinition['name'];
            }

            $schema['properties']{$paramDefinition['name']} = $propertySchema;
        }

        /**
         * TODO Hack, probably not the best performing of solutions
         * @see https://github.com/kleijnweb/swagger-bundle/issues/29
         */

        return json_decode(json_encode($schema));
    }

    /**
     * @param Request $request
     * @param array   $operationDefinition
     *
     * @return \stdClass
     * @throws UnsupportedException
     */
    private function assembleParameterDataForValidation(Request $request, array $operationDefinition)
    {
        if (!isset($operationDefinition['parameters'])) {
            return new \stdClass;
        }

        /**
         * TODO Hack
         * @see https://github.com/kleijnweb/swagger-bundle/issues/24
         */
        $content = null;
        if ($request->getContent()) {
            $decoder = new JsonDecode(false);
            $content = (object)$decoder->decode($request->getContent(), 'json');
        }

        $parameters = [];

        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'body'   => 'attributes',
            'header' => 'headers'
        ];
        foreach ($operationDefinition['parameters'] as $paramDefinition) {
            $paramName = $paramDefinition['name'];

            if (!isset($paramBagMapping[$paramDefinition['in']])) {
                throw new UnsupportedException(
                    "Unsupported parameter 'in' value in definition '{$paramDefinition['in']}'"
                );
            }
            if (!$request->attributes->has($paramName)) {
                continue;
            }
            if ($paramDefinition['in'] === 'body' && $content) {
                $parameters[$paramName] = $content;
                continue;
            }
            $parameters[$paramName] = $request->attributes->get($paramName);

            /**
             * TODO Hack for date- datetime validation after already coerced into objects
             * @see https://github.com/kleijnweb/swagger-bundle/issues/24
             */
            if (isset($paramDefinition['format'])) {
                if ($paramDefinition['format'] === 'date') {
                    $parameters[$paramName] = $parameters[$paramName]->format('Y-m-d');
                }
                if ($paramDefinition['format'] === 'date-time') {
                    $parameters[$paramName] = $parameters[$paramName]->format(\DateTime::W3C);
                }
            }
        }

        /**
         * TODO Hack, probably not the best performing of solutions
         * @see https://github.com/kleijnweb/swagger-bundle/issues/29
         */

        return (object)json_decode(json_encode($parameters));
    }
}
