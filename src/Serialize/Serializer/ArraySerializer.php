<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Serialize\Serializer;

/**
 * Simply utilizes json_encode/json_decode
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ArraySerializer implements Serializer
{
    /**
     * @param mixed $data
     *
     * @return string
     */
    public function serialize($data): string
    {
        return json_encode($data);
    }

    /**
     * @param mixed       $data
     * @param string|null $type
     *
     * @return mixed
     */
    public function deserialize($data, string $type = null)
    {
        $array = json_decode($data, true);

        if (!is_array($array)) {
            throw new \UnexpectedValueException("Expected result to be an array");
        }

        return $array;
    }
}