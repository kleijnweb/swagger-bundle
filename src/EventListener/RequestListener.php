<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\SwaggerBundle\Middleware\RequestMiddleware;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListener
{
    /**
     * RequestListener constructor.
     *
     * @param RequestMiddleware $middleware
     */
    public function __construct(RequestMiddleware $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $psr7Factory    = new DiactorosFactory();
        $symfonyRequest = $event->getRequest();
        $psrRequest     = $psr7Factory->createRequest($symfonyRequest);
        $psrRequest     = $this->middleware->process($psrRequest);

        foreach ($psrRequest->getAttributes() as $name => $value) {
            $symfonyRequest = $symfonyRequest->attributes;
        }

        // convert a Request
// $psrRequest is an instance of Psr\Http\Message\ServerRequestInterface

    }
}
