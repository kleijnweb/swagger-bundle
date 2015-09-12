<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Exception\UnsupportedException;
use KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder;
use KleijnWeb\SwaggerBundle\Request\Transformer\ParameterCoercer;
use Symfony\Component\HttpFoundation\Request;
use JsonSchema\Validator;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedContentTypeException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestTransformer
{
    /**
     * @var ContentDecoder
     */
    private $contentDecoder;

    /**
     * @param ContentDecoder $contentDecoder
     */
    public function __construct(ContentDecoder $contentDecoder)
    {
        $this->contentDecoder = $contentDecoder;
    }

    /**
     * @param Request $request
     * @param array   $operationDefinition
     *
     * @throws InvalidParametersException
     * @throws MalformedContentException
     * @throws UnsupportedContentTypeException
     */
    public function coerceRequest(Request $request, array $operationDefinition)
    {
        $content = $this->contentDecoder->decodeContent($request, $operationDefinition);

        // This modifies the Request object
        $this->coerceRequestParameters($request, $operationDefinition);

        // This retrieves the modified parameters from the request object and content
        $parameters = $this->assembleParameterData($request, $operationDefinition, $content);

        // Validate the parameters using a schema created from the operation definition
        $validator = new Validator();
        $schema = $this->assembleRequestSchema($operationDefinition);
        $validator->check($parameters, $schema);

        if (!$validator->isValid()) {
            // TODO Better utilize $validator->getErrors() so we can assemble a more helpful vnd.error response
            throw new InvalidParametersException(
                "Parameters incompatible with operation schema: " . implode(', ', $validator->getErrors()[0]),
                400
            );
        }

        // Needed to be able to set the decoded body
        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $content
        );
    }

    /**
     * @param array $operationDefinition
     *
     * @return object
     */
    public function assembleRequestSchema(array $operationDefinition)
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

        // TODO Hack, probably not the best performing of solutions
        return json_decode(json_encode($schema));
    }

    /**
     * @param Request $request
     * @param array   $operationDefinition
     *
     * @throws MalformedContentException
     * @throws UnsupportedException
     */
    public function coerceRequestParameters(Request $request, array $operationDefinition)
    {
        if (!isset($operationDefinition['parameters'])) {
            return;
        }
        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'header' => 'headers'
        ];
        foreach ($operationDefinition['parameters'] as $paramDefinition) {
            $paramName = $paramDefinition['name'];

            if ($paramDefinition['in'] === 'body') {
                continue;
            }

            if (!isset($paramBagMapping[$paramDefinition['in']])) {
                throw new UnsupportedException(
                    "Unsupported parameter 'in' value in definition '{$paramDefinition['in']}'"
                );
            }
            $paramBagName = $paramBagMapping[$paramDefinition['in']];
            if (!$request->$paramBagName->has($paramName)) {
                continue;
            }
            $request->$paramBagName->set(
                $paramName,
                ParameterCoercer::coerceParameter(
                    $paramDefinition,
                    $request->$paramBagName->get($paramName)
                )
            );
        }
    }

    /**
     * @param Request $request
     * @param array   $operationDefinition
     * @param mixed   $content
     *
     * @return \stdClass
     * @throws UnsupportedException
     */
    public function assembleParameterData(Request $request, array $operationDefinition, $content = null)
    {
        if (!isset($operationDefinition['parameters'])) {
            return new \stdClass;
        }
        $parameters = [];

        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'header' => 'headers'
        ];
        foreach ($operationDefinition['parameters'] as $paramDefinition) {
            $paramName = $paramDefinition['name'];

            if ($paramDefinition['in'] === 'body') {
                // This should be handled by the schema validation
                $parameters[$paramName] = $content ? $content : null;
                continue;
            }

            if (!isset($paramBagMapping[$paramDefinition['in']])) {
                throw new UnsupportedException(
                    "Unsupported parameter 'in' value in definition '{$paramDefinition['in']}'"
                );
            }
            $paramBagName = $paramBagMapping[$paramDefinition['in']];
            if (!$request->$paramBagName->has($paramName)) {
                continue;
            }
            if (!$request->query->has($paramName)) {
                continue;
            }
            $parameters[$paramName] = $request->query->get($paramName);
        }

        return (object)$parameters;
    }
}
