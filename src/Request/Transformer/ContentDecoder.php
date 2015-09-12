<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request\Transformer;

use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedContentTypeException;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ContentDecoder
{
    /**
     * @var SerializerAdapter
     */
    private $serializer;

    /**
     * @var string
     */
    private $resourceNamespace;

    /**
     * @param SerializerAdapter $serializer
     * @param string            $resourceNamespace
     */
    public function __construct(SerializerAdapter $serializer, $resourceNamespace = null)
    {
        $this->serializer = $serializer;
        $this->resourceNamespace = $resourceNamespace;
    }

    /**
     * @param Request $request
     * @param array   $operationDefinition
     *
     * @return mixed|null
     * @throws MalformedContentException
     * @throws UnsupportedContentTypeException
     */
    public function decodeContent(Request $request, array $operationDefinition)
    {
        if ($content = $request->getContent()) {
            $type = null;
            if (isset($operationDefinition['parameters'])) {
                foreach ($operationDefinition['parameters'] as $parameterDefinition) {
                    if ($parameterDefinition['in'] == 'body') {
                        $reference = isset($parameterDefinition['schema']['$ref'])
                            ? $parameterDefinition['schema']['$ref']
                            : $parameterDefinition['schema']['id'];
                        $type = ltrim(
                            $this->resourceNamespace . '\\' . substr($reference, strrpos($reference, '/') + 1),
                            '\\'
                        );
                    }
                }
            }

            try {
                return $this->serializer->deserialize($content, $type, $request->getContentType());
            } catch (\Exception $e) {
                throw new MalformedContentException("Unable to decode payload", 400, $e);
            }
        }

        return null;
    }
}
