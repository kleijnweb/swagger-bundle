<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\Response\ResponseFactory;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedContentTypeException;
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
     * @throws UnsupportedContentTypeException
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has(RequestMeta::ATTRIBUTE_URI)) {
            return;
        }
        $event->setResponse(
            $this->responseFactory->createResponse(
                $request,
                $event->getControllerResult()
            )
        );
    }
}
