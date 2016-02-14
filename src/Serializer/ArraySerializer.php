<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serializer;

/**
 * Simply utilizes json_encode/json_decode
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ArraySerializer
{
    /**
     * @param mixed $data
     *
     * @return string
     */
    public function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * @param mixed $data
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function deserialize($data)
    {
        $array = json_decode($data, true);

        if (!is_array($array)) {
            throw new \UnexpectedValueException("Expected result to be an array");
        }

        return $array;
    }
}
