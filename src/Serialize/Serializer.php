<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize;

use KleijnWeb\SwaggerBundle\Document\Specification;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface Serializer
{
    /**
     * @param mixed         $data
     * @param Specification $specification
     *
     * @return string
     */
    public function serialize($data, Specification $specification): string;

    /**
     * @param mixed         $data
     * @param string        $type
     * @param Specification $specification
     *
     * @return mixed
     */
    public function deserialize($data, string $type, Specification $specification);
}
