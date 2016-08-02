<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Document\Specification\Operation;
use KleijnWeb\SwaggerBundle\Request\RequestMeta;
use KleijnWeb\SwaggerBundle\Request\RequestProcessor;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;
use KleijnWeb\SwaggerBundle\Serialize\TypeResolver\SerializerTypeDefinitionMapBuilder;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListener
{
    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @var RequestProcessor
     */
    private $processor;

    /**
     * @var SerializerTypeDefinitionMapBuilder
     */
    private $serializerTypeDefinitionMapBuilder;

    /**
     * RequestListener constructor.
     *
     * @param DocumentRepository                      $schemaRepository
     * @param RequestProcessor                        $processor
     * @param SerializerTypeDefinitionMapBuilder|null $serializerTypeDefinitionMapBuilder
     */
    public function __construct(
        DocumentRepository $schemaRepository,
        RequestProcessor $processor,
        SerializerTypeDefinitionMapBuilder $serializerTypeDefinitionMapBuilder = null
    ) {
        $this->documentRepository                 = $schemaRepository;
        $this->processor                          = $processor;
        $this->serializerTypeDefinitionMapBuilder = $serializerTypeDefinitionMapBuilder;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (!$request->attributes->get('_swagger.file')) {
            return;
        }
        if (!$request->get('_swagger.path')) {
            throw new \LogicException("Request does not contain reference to Swagger path");
        }

        $specification = $this->documentRepository->get($request->attributes->get('_swagger.file'));
        $operation     = $specification->getOperation(
            $request->attributes->get('_swagger.path'),
            $request->getMethod()
        );

        $request->attributes->set('_swagger.meta', $this->createRequestMeta($specification, $operation));

        $this->processor->process($request, $operation);
    }

    /**
     * @param Specification $specification
     * @param Operation     $operation
     *
     * @return RequestMeta
     */
    private function createRequestMeta(Specification $specification, Operation $operation): RequestMeta
    {
        if ($this->serializerTypeDefinitionMapBuilder) {
            return new RequestMeta(
                $specification,
                $operation,
                $this->serializerTypeDefinitionMapBuilder->build($specification)
            );
        }

        return new RequestMeta($specification, $operation);
    }
}
