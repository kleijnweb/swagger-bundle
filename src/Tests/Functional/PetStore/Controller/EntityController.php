<?php
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
class EntityController
{
    /**
     * @param string    $type
     * @param \DateTime $lastModified
     *
     * @return array
     */
    public function find($type, \DateTime $lastModified)
    {
        return [
            [
                'id'   => 2,
                'type' => $type,
                'foo'  => 'bar'
            ]
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $type
     * @param array  $criteria
     *
     * @return array
     */
    public function findByCriteria($type, array $criteria)
    {
        $entities = [];

        foreach ($criteria as $i => $criterion) {
            $entities[] = [
                'id'   => $i + 3,
                'type' => $type,
                'foo'  => 'bar'
            ];
        }

        return $entities;
    }

    /**
     * @param string $type
     * @param int    $id
     *
     * @return array
     */
    public function get($type, $id)
    {
        return [
            'id'   => $id,
            'type' => $type,
            'foo'  => 'bar'
        ];
    }

    /**
     * @param string $type
     * @param int    $id
     * @param array  $data
     *
     * @return array
     */
    public function put($type, $id, $data)
    {
        $data['id'] = $id;
        $data['type'] = $type;

        return $data;
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @return array
     */
    public function post($type, $data)
    {
        $data['id'] = rand();
        $data['type'] = $type;

        return $data;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $type
     * @param int    $id
     *
     * @return null
     */
    public function delete($type, $id)
    {
        return null;
    }
}
