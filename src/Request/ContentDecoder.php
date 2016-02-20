<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Serializer\SerializationTypeResolver;
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
     * @var SerializationTypeResolver
     */
    private $typeResolver;

    /**
     * ContentDecoder constructor.
     *
     * @param SerializerAdapter              $serializer
     * @param SerializationTypeResolver|null $typeResolver
     */
    public function __construct(SerializerAdapter $serializer, SerializationTypeResolver $typeResolver = null)
    {
        $this->serializer = $serializer;
        $this->setTypeResolver($typeResolver);
        $this->typeResolver = $typeResolver;
    }

    /**
     * @param SerializationTypeResolver|null $typeResolver
     *
     * @return ContentDecoder
     */
    public function setTypeResolver(SerializationTypeResolver $typeResolver = null): ContentDecoder
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }

    /**
     * @param Request         $request
     * @param OperationObject $operationObject
     *
     * @return mixed
     * @throws MalformedContentException
     */
    public function decodeContent(Request $request, OperationObject $operationObject)
    {
        if ($content = $request->getContent()) {
            $type = $this->typeResolver ? $this->typeResolver->resolve($operationObject) : null;
            try {
                return $this->serializer->deserialize($content, $type, 'json');
            } catch (\Throwable $e) {
                throw new MalformedContentException("Unable to decode payload", 400, $e);
            }
        }

        return null;
    }
}
