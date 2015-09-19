<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Request\ContentDecoder;
use Symfony\Component\HttpFoundation\Request;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedContentTypeException;
use Symfony\Component\Serializer\Encoder\JsonDecode;

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
        $this->validator = new RequestValidator();
        $this->coercer = new RequestCoercer();
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
        /**
         * TODO Hack
         * @see https://github.com/kleijnweb/swagger-bundle/issues/24
         */
        $originalContent = $request->getContent();
        $stdClassContent = null;
        if ($originalContent) {
            $decoder = new JsonDecode(false);
            $stdClassContent = (object)$decoder->decode($originalContent, 'json');
        }

        $content = $this->contentDecoder->decodeContent($request, $operationDefinition);

        // This modifies the Request object (and adds the content to the 'attributes' ParameterBag
        $this->coercer->coerceRequestParameters($request, $operationDefinition, $content);

        $this->validator->validateRequest($request, $operationDefinition, $stdClassContent);

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
}
