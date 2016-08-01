<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use JsonSchema\Validator;
use KleijnWeb\SwaggerBundle\Document\Specification\Operation;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestValidator
{
    /**
     * @var Operation
     */
    private $operationObject;

    /**
     * @param Operation $operationObject
     */
    public function __construct(Operation $operationObject = null)
    {
        if ($operationObject) {
            $this->setOperationObject($operationObject);
        }
    }

    /**
     * @param Operation $operationObject
     *
     * @return $this
     */
    public function setOperationObject(Operation $operationObject)
    {
        $this->operationObject = $operationObject;

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
            $this->operationObject->getRequestSchema()
        );

        if (!$validator->isValid()) {
            throw new InvalidParametersException(
                "Parameters incompatible with operation schema: "
                . implode(', ', $validator->getErrors()[0]),
                $validator->getErrors()
            );
        }
    }

    /**
     * @param Request $request
     *
     * @return \stdClass
     * @throws UnsupportedException
     */
    private function assembleParameterDataForValidation(Request $request)
    {
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

        if (isset($this->operationObject->getDefinition()->parameters)) {
            foreach ($this->operationObject->getDefinition()->parameters as $paramDefinition) {
                $paramName = $paramDefinition->name;

                if (!$request->attributes->has($paramName)) {
                    continue;
                }
                if ($paramDefinition->in === 'body' && $content !== null) {
                    $parameters->$paramName = $content;
                    continue;
                }
                $parameters->$paramName = $request->attributes->get($paramName);

                /**
                 * If value already coerced into \DateTime object, get the raw value for validation instead
                 *
                 * TODO Keep raw value of attributes around
                 */
                if ($parameters->$paramName instanceof \DateTime) {
                    if ($paramDefinition->in === 'query') {
                        $parameters->$paramName = $request->query->get($paramName);
                    } elseif ($paramDefinition->in === 'header') {
                        $parameters->$paramName = $request->headers->get($paramName);
                    } elseif ($paramDefinition->in === 'path') {
                        if ($paramDefinition->format === 'date') {
                            $parameters->$paramName = $parameters->$paramName->format('Y-m-d');
                        }
                        if ($paramDefinition->format === 'date-time') {
                            $parameters->$paramName = $parameters->$paramName->format(\DateTime::W3C);
                        }
                    }
                }
            }
        }

        return $parameters;
    }
}
