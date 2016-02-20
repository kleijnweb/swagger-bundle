<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\SwaggerBundle\Response\ResponseFactory;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ViewListener
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * ViewListener constructor.
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws MalformedContentException
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $result = $event->getControllerResult();
        $response = $this->responseFactory->createResponse(
            $event->getRequest(),
            $result
        );

        $event->setResponse($response);
    }
}
