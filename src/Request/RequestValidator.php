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

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestValidator
{
    /**
     * @var array
     */
    private $operationDefinition;

    /**
     * @param array $operationDefinition
     */
    public function __construct(array $operationDefinition)
    {
        $this->operationDefinition = $operationDefinition;
    }

    /**
     * @param Request $request
     *
     * @throws InvalidParametersException
     * @throws UnsupportedException
     */
    public function validateRequest(Request $request)
    {
        // This retrieves the modified parameters
        $parameters = $this->assembleParameterDataForValidation($request);

        // Validate the parameters using a schema created from the operation definition
        $validator = new Validator();
        $schema = $this->assembleRequestSchema();
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
     * @return object
     */
    private function assembleRequestSchema()
    {
        if (!isset($this->operationDefinition['parameters'])) {
            return new \stdClass;
        }

        $schema = [
            'type'       => 'object',
            'properties' => [],
            'required'   => []
        ];

        foreach ($this->operationDefinition['parameters'] as $paramDefinition) {
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
     *
     * @return \stdClass
     * @throws UnsupportedException
     */
    private function assembleParameterDataForValidation(Request $request)
    {
        if (!isset($this->operationDefinition['parameters'])) {
            return new \stdClass;
        }

        /**
         * TODO Hack
         * @see https://github.com/kleijnweb/swagger-bundle/issues/24
         */
        $content = null;
        if ($request->getContent()) {
            $content = (object)json_decode($request->getContent());
        }

        $parameters = [];

        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'body'   => 'attributes',
            'header' => 'headers'
        ];
        foreach ($this->operationDefinition['parameters'] as $paramDefinition) {
            $paramName = $paramDefinition['name'];

            if (!isset($paramBagMapping[$paramDefinition['in']])) {
                throw new UnsupportedException(
                    "Unsupported parameter 'in' value in definition '{$paramDefinition['in']}'"
                );
            }
            if (!$request->attributes->has($paramName)) {
                continue;
            }
            if ($paramDefinition['in'] === 'body' && $content !== null) {
                $parameters[$paramName] = $content;
                continue;
            }
            $parameters[$paramName] = $request->attributes->get($paramName);

            /**
             * If value already coerced into \DateTime object, use any non-empty value for validation
             */
            if ($parameters[$paramName] instanceof \DateTime) {
                if (isset($paramDefinition['format'])) {
                    if ($paramDefinition['format'] === 'date') {
                        $parameters[$paramName] = '1970-01-01';
                    }
                    if ($paramDefinition['format'] === 'date-time') {
                        $parameters[$paramName] = '1970-01-01T00:00:00Z';
                    }
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
