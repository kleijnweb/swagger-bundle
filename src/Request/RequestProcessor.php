<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedContentTypeException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestProcessor
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
        $this->validator = new RequestValidator();
        $this->coercer = new RequestCoercer($contentDecoder);
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
        $this->coercer->coerceRequest($request, $operationDefinition);
        $this->validator->validateRequest($request, $operationDefinition);
    }
}
