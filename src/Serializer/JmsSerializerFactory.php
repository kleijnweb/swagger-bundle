<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serializer;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

/**
 * Creates a JMS Serializer
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JmsSerializerFactory
{
    /**
     * @return Serializer
     */
    public static function factory()
    {
        return SerializerBuilder::create()->build();
    }
}
