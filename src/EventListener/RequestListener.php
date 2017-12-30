<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestProcessor;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListener
{
    /**
     * @var RequestProcessor
     */
    private $processor;

    /**
     * RequestListener constructor.
     *
     * @param RequestProcessor $processor
     */
    public function __construct(RequestProcessor $processor)
    {
        $this->processor = $processor;
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
        if (!$request->attributes->has(RequestMeta::ATTRIBUTE_URI)) {
            return;
        }
        $this->processor->process($request);
    }
}
