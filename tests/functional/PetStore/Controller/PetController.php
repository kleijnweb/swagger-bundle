<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Controller;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class PetController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return array
     */
    public function findPetsByStatus()
    {
        return [
            (object)[
                'id'        => 1,
                'name'      => 'Scooby',
                'photoUrls' => []
            ]
        ];
    }

    /**
     * @param \stdClass $body
     *
     * @return \stdClass
     */
    public function addPet(\stdClass $body)
    {
        $body->status = 'available';

        return $body;
    }

    /**
     * @param int $petId
     *
     * @return \stdClass
     */
    public function getPetById(int $petId)
    {
        return (object)[
            'id'        => $petId,
            'name'      => 'Chuckie',
            'photoUrls' => []
        ];
    }
}
