<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Document\Specification\Operation;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedContentTypeException;
use KleijnWeb\SwaggerBundle\Serialize\SerializationTypeResolver;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ContentDecoder
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var SerializationTypeResolver
     */
    private $typeResolver;

    /**
     * @param Serializer                $serializer
     * @param SerializationTypeResolver $typeResolver
     */
    public function __construct(Serializer $serializer, SerializationTypeResolver $typeResolver = null)
    {
        $this->serializer   = $serializer;
        $this->typeResolver = $typeResolver;
    }

    /**
     * @param Request   $request
     * @param Operation $operationObject
     *
     * @return mixed
     * @throws MalformedContentException
     * @throws UnsupportedContentTypeException
     */
    public function decodeContent(Request $request, Operation $operationObject)
    {
        if ($content = $request->getContent()) {
            try {
                $type = $this->typeResolver ? $this->typeResolver->resolveOperationBodyType($operationObject) : '';

                return $this->serializer->deserialize($content, $type);
            } catch (\Exception $e) {
                throw new MalformedContentException("Unable to decode payload", 400, $e);
            }
        }

        return null;
    }
}
