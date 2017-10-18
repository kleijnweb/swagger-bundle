<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactory
{
    /**
     * @var SerializerAdapter
     */
    private $serializer;

    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @param DocumentRepository $documentRepository
     * @param SerializerAdapter  $serializer
     */
    public function __construct(DocumentRepository $documentRepository, SerializerAdapter $serializer)
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
        if (!$request->get('_definition')) {
            throw new \LogicException("Request does not contain reference to definition");
        }
        if (!$request->get('_swagger_path')) {
            throw new \LogicException("Request does not contain reference to Swagger path");
        }

        if ($data !== null) {
            $data = $this->serializer->serialize($data, 'json');
        }

        $swaggerDocument = $this->documentRepository->get($request->get('_definition'));

        $operationDefinition = $swaggerDocument
            ->getOperationDefinition(
                $request->get('_swagger_path'),
                $request->getMethod()
            );

        $responseCode = 200;
        $understands204 = false;
        foreach (array_keys((array)$operationDefinition->responses) as $statusCode) {
            if ($statusCode == 204) {
                $understands204 = true;
                break;
            }
        }
        foreach (array_keys((array)$operationDefinition->responses) as $statusCode) {
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
