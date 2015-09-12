<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Controller;

use KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Resources\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class StoreController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Request $request
     *
     * @return Order
     */
    public function placeOrder(Request $request)
    {
        /** @var Order $order */
        $order = $request->getContent();

        return $order
            ->setId(rand())
            ->setStatus('placed');
    }
}
