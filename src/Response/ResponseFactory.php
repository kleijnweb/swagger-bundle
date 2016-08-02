<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Request\RequestMeta;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactory
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
     * @param DocumentRepository $documentRepository
     * @param Serializer         $serializer
     */
    public function __construct(
        DocumentRepository $documentRepository,
        Serializer $serializer
    ) {
        $this->serializer         = $serializer;
        $this->documentRepository = $documentRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Request $request
     * @param mixed   $data
     *
     * @return Response
     */
    public function createResponse(Request $request, $data)
    {
        /** @var RequestMeta $meta */
        $meta = $request->attributes->get('_swagger.meta');
        $specification = $meta->getSpecification();

        if ($data !== null) {
            $data = $this->serializer->serialize($data, $meta->getDefinitionMap());
        }

        $operation = $specification
            ->getOperation(
                $request->get('_swagger.path'),
                $request->getMethod()
            );

        $responseCode   = 200;
        $understands204 = false;
        foreach ($operation->getResponseCodes() as $statusCode) {
            if ($statusCode == 204) {
                $understands204 = true;
            }
            if (2 == substr($statusCode, 0, 1)) {
                $responseCode = $statusCode;
                break;
            }
        }

        if ($data === null && $understands204) {
            $responseCode = 204;
        }

        return new Response($data, $responseCode, ['Content-Type' => 'application/json']);
    }
}
