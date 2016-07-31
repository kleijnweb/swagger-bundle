<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize\Serializer;

use JMS\Serializer\SerializerInterface as JmsSerializer;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;

/**
 * Adapter for JMS\Serializer
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JmsSerializerAdapter implements Serializer
{
    /**
     * @var JmsSerializer
     */
    private $target;

    /**
     * JmsSerializerAdapter constructor.
     *
     * @param JmsSerializer $target
     */
    public function __construct(JmsSerializer $target)
    {
        $this->target = $target;
    }

    /**
     * @param mixed $data any data
     *
     * @return string
     */
    public function serialize($data): string
    {
        return $this->target->serialize($data, 'json');
    }

    /**
     * Deserializes data into the given type.
     *
     * @param mixed  $data
     * @param string $type
     *
     * @return object|array
     */
    public function deserialize($data, string $type)
    {
        return $this->target->deserialize($data, $type, 'json');
    }
}
