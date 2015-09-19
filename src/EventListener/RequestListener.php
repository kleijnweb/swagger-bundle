<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Request\RequestProcessor;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListener
{
    /**
     * @var DocumentRepository
     */
    private $schemaRepository;

    /**
     * @var RequestProcessor
     */
    private $transformer;

    /**
     * @param DocumentRepository $schemaRepository
     * @param RequestProcessor $transformer
     */
    public function __construct(DocumentRepository $schemaRepository, RequestProcessor $transformer)
    {
        $this->schemaRepository = $schemaRepository;
        $this->transformer = $transformer;
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
        $swaggerDocument = $this->schemaRepository->get($request->get('_definition'));

        $operationDefinition = $swaggerDocument
            ->getOperationDefinition(
                $request->getPathInfo(),
                $request->getMethod()
            );

        $this->transformer->process(
            $request,
            $operationDefinition
        );
    }
}
