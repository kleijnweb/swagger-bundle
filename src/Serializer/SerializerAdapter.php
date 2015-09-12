<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serializer;

use JMS\Serializer\SerializationContext;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializer;
use JMS\Serializer\SerializerInterface as JmsSerializer;

/**
 * Adapter for transparent use of ArraySerializer, Symfony\Component\Serializer or JMS\Serializer
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SerializerAdapter
{
    /**
     * @var SymfonySerializer|JmsSerializer|ArraySerializer
     */
    private $target;

    /**
     * @var bool
     */
    private $expectsEmptyArray = false;

    /**
     * @param SymfonySerializer|JmsSerializer|ArraySerializer $target
     */
    public function __construct($target)
    {
        $this->setTarget($target);
    }

    /**
     * @param SymfonySerializer|JmsSerializer|ArraySerializer $target
     *
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
        if ($target instanceof SymfonySerializer) {
            $this->expectsEmptyArray = true;
        }

        return $this;
    }

    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed                      $data    any data
     * @param string                     $format  format name
     * @param SerializationContext|array $context options normalizers/encoders have access to
     *
     * @return string
     */
    public function serialize($data, $format, $context = null)
    {
        return $this->target->serialize($data, $format, $context ? $context : ($this->expectsEmptyArray ? [] : null));
    }

    /**
     * Deserializes data into the given type.
     *
     * @param mixed                      $data
     * @param string                     $type
     * @param string                     $format
     * @param SerializationContext|array $context
     *
     * @return object|array
     */
    public function deserialize($data, $type, $format, $context = null)
    {
        return $this->target->deserialize(
            $data,
            $type,
            $format,
            $context ? $context : ($this->expectsEmptyArray ? [] : null)
        );
    }
}
