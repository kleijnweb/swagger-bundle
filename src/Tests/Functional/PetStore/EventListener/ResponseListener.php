<?php
/*
 * This file is part of the KleijnWeb\RestETagBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseListener
{
    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        $headers = $event->getResponse()->headers;
        switch ($request->attributes->get('_swagger_path')) {
            case '/user/login':
                $headers->set('X-Rate-Limit', 123456789);
                $headers->set('X-Expires-After', date('Y-m-d\TH:i:s\Z'));
                break;
            default:
                //noop
        }
    }
}
