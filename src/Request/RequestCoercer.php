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
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestCoercer
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
     * @param object   $operationDefinition
     *
     * @throws MalformedContentException
     * @throws UnsupportedException
     */
    public function coerceRequest(Request $request, $operationDefinition)
    {
        $content = $this->contentDecoder->decodeContent($request, $operationDefinition);

        if (!isset($operationDefinition->parameters)) {
            return;
        }
        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'header' => 'headers'
        ];
        foreach ($operationDefinition->parameters as $paramDefinition) {
            $paramName = $paramDefinition->name;

            if ($paramDefinition->in === 'body') {
                if ($content !== null) {
                    $request->attributes->set($paramName, $content);
                }

                continue;
            }

            if (!isset($paramBagMapping[$paramDefinition->in])) {
                throw new UnsupportedException(
                    "Unsupported parameter 'in' value in definition '{$paramDefinition->in}'"
                );
            }
            $paramBagName = $paramBagMapping[$paramDefinition->in];
            if (!$request->$paramBagName->has($paramName)) {
                continue;
            }
            $request->attributes->set(
                $paramName,
                ParameterCoercer::coerceParameter(
                    $paramDefinition,
                    $request->$paramBagName->get($paramName)
                )
            );
        }
    }
}
