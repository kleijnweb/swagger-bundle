<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Controller;

use KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Resources\Order;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class StoreController
{
    /**
     * @param Order $body
     *
     * @return Order
     */
    public function placeOrder(Order $body): Order
    {
        return $body
            ->setId(rand())
            ->setComplete(true)
            ->setShipDate($body->getShipDate()->add(new \DateInterval('P1D')))
            ->setStatus('placed');
    }
}
