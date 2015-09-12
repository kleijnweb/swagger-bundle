<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactory
{
    /**
     * @var SerializerInterface
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
    public function __construct(DocumentRepository $documentRepository, Serializer $serializer)
    {
        $this->serializer = $serializer;
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
        $this->serializer->serialize($data, 'json');

        return new JsonResponse($data, 200);
    }
}
