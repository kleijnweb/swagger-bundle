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
    private $documentRepository;

    /**
     * @var RequestProcessor
     */
    private $processor;

    /**
     * @param DocumentRepository $schemaRepository
     * @param RequestProcessor   $processor
     */
    public function __construct(DocumentRepository $schemaRepository, RequestProcessor $processor)
    {
        $this->documentRepository = $schemaRepository;
        $this->processor          = $processor;
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

        if (!$definition = $request->get('_definition')) {
            return;
        }
        if (!$swaggerPath = $request->get('_swagger_path')) {
            throw new \LogicException("Request does not contain reference to Swagger path");
        }

        $swaggerDocument = $this->documentRepository->get($definition);

        $operation = $swaggerDocument->getOperationObject($swaggerPath, $request->getMethod());

        if (isset($operation->getDefinition()->{'consumes'})) {
            $types = array_map(function ($type) {
                return preg_replace('#(.*)/([a-z\.]+\+)?(.*?)#', '$3', $type);
            }, $operation->getDefinition()->{'consumes'});

            if (!in_array('json', $types)) {
                return;
            }
        }

        $request->attributes->set('_swagger_operation', $operation);
        $request->attributes->set('_swagger_document', $swaggerDocument);

        $this->processor->process($request, $operation);
    }
}
