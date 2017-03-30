<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Controller;

use KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Resources\Pet;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class HydratedPetController
{
    /**
     * @param Pet $body
     *
     * @return Pet
     */
    public function addPet(Pet $body): Pet
    {
        $body->setId(rand());
        $body->getCategory()->setId(rand());

        foreach ($body->getTags() as $tag) {
            $tag->setId(rand());
        };

        return $body;
    }
}
