<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
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
     * @param Request         $request
     * @param OperationObject $operationObject
     *
     * @throws MalformedContentException
     * @throws UnsupportedException
     */
    public function coerceRequest(Request $request, OperationObject $operationObject)
    {
        $content = $this->contentDecoder->decodeContent($request, $operationObject);

        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'header' => 'headers'
        ];

        if(isset($operationObject->getDefinition()->parameters)) {
            foreach ($operationObject->getDefinition()->parameters as $paramDefinition) {
                $paramName = $paramDefinition->name;

                if ($paramDefinition->in === 'body') {
                    if ($content !== null) {
                        $request->attributes->set($paramName, $content);
                    }

                    continue;
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
}
