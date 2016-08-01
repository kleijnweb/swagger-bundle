<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\Specification\Operation;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedContentTypeException;
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
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @param Serializer         $serializer
     * @param DocumentRepository $documentRepository
     */
    public function __construct(
        Serializer $serializer,
        DocumentRepository $documentRepository
    ) {
        $this->serializer         = $serializer;
        $this->documentRepository = $documentRepository;
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
            if (!$request->attributes->get('_swagger.file')) {
                throw new \LogicException("Request does not contain reference to definition");
            }
            /** @var RequestMeta $meta */
            $meta = $request->get('_swagger.meta');

            $type = ($definitionMap = $meta->getDefinitionMap())
                ? $definitionMap->getFqcn(
                    $definitionMap->getTypeNameByOperationId($meta->getOperation()->getId())
                )
                : '';

            try {
                return $this->serializer->deserialize($content, $type, $definitionMap);
            } catch (\Exception $e) {
                throw new MalformedContentException("Unable to decode payload", 400, $e);
            }
        }

        return null;
    }
}
