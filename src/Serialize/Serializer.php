<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface Serializer
{
    /**
     * @param mixed $data
     *
     * @return string
     */
    public function serialize($data): string;

    /**
     * @param mixed  $data
     * @param string $type
     *
     * @return mixed
     */
    public function deserialize($data, string $type);
}
