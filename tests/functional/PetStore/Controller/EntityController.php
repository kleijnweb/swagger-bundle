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
class EntityController
{
    /**
     * @return \stdClass
     */
    public function getStatus()
    {
        return (object)[
            'overall' => 'ok'
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string    $type
     * @param \DateTime $lastModified
     *
     * @return array
     */
    public function find(string $type, \DateTime $lastModified)
    {
        return [
            (object)[
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
    public function findByCriteria(string $type, array $criteria)
    {
        $entities = [];

        foreach ($criteria as $i => $criterion) {
            $entities[] = (object)[
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
     * @return \stdClass
     */
    public function get(string $type, int $id)
    {
        return (object)[
            'id'   => $id,
            'type' => $type,
            'foo'  => 'bar'
        ];
    }

    /**
     * @param string    $type
     * @param int       $id
     * @param \stdClass $data
     *
     * @return \stdClass
     */
    public function put(string $type, int $id, \stdClass $data)
    {
        $data->id   = $id;
        $data->type = $type;

        return $data;
    }

    /**
     * @param string    $type
     * @param \stdClass $data
     *
     * @return \stdClass
     */
    public function post(string $type, \stdClass $data)
    {
        $data->id   = rand();
        $data->type = $type;

        return $data;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $type
     * @param int    $id
     *
     * @return null
     */
    public function delete(string $type, int $id)
    {
        return null;
    }
}
