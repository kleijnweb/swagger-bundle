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
    private $operationDefinition = [];

    /**
     * @param array $operationDefinition
     */
    public function __construct($operationDefinition = [])
    {
        $this->operationDefinition = $operationDefinition;
    }

    /**
     * @param array $operationDefinition
     *
     * @return $this
     */
    public function setOperationDefinition($operationDefinition)
    {
        $this->operationDefinition = $operationDefinition;

        return $this;
    }

    /**
     * @param Request $request
     *
     * @throws InvalidParametersException
     * @throws UnsupportedException
     */
    public function validateRequest(Request $request)
    {
        $validator = new Validator();

        $validator->check(
            $this->assembleParameterDataForValidation($request),
            $this->assembleRequestSchema()
        );

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
        $schema = new \stdClass;
        $schema->type = 'object';
        $schema->required = [];
        $schema->properties = new \stdClass;

        foreach ($this->operationDefinition['parameters'] as $paramDefinition) {
            if (isset($paramDefinition['required']) && $paramDefinition['required']) {
                $schema->required[] = $paramDefinition['name'];
            }

            if ($paramDefinition['in'] === 'body') {
                $schema->properties->{$paramDefinition['name']} = $this->arrayToObject($paramDefinition['schema']);
                continue;
            }
            $propertySchema = ['type' => $paramDefinition['type']];

            $schema->properties->{$paramDefinition['name']} = $this->arrayToObject($propertySchema);
        }

        return $schema;
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
            $content = json_decode($request->getContent());
            //TODO UT this
            $content = (is_array($content) && isset($content[0])) ? $content : (object)$content;
        }

        $parameters = new \stdClass;

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
                $parameters->$paramName = $content;
                continue;
            }
            $parameters->$paramName = $request->attributes->get($paramName);

            /**
             * If value already coerced into \DateTime object, use any non-empty value for validation
             */
            if ($parameters->$paramName instanceof \DateTime) {
                if (isset($paramDefinition['format'])) {
                    if ($paramDefinition['format'] === 'date') {
                        $parameters->$paramName = '1970-01-01';
                    }
                    if ($paramDefinition['format'] === 'date-time') {
                        $parameters->$paramName = '1970-01-01T00:00:00Z';
                    }
                }
            }
        }

        return $parameters;
    }

    /**
     * @see https://github.com/kleijnweb/swagger-bundle/issues/29
     *
     * @param array $data
     *
     * @return object
     */
    private static function arrayToObject(array $data)
    {
        $object = new \stdClass;
        foreach ($data as $key => $value) {
            $object->$key = is_array($value) ? self::arrayToObject($value) : $value;
        }

        return $object;
    }
}
