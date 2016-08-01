<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;
use KleijnWeb\SwaggerBundle\Serialize\TypeResolver\SerializerTypeDefinitionMap;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializer;

/**
 * Adapter for transparent JMS\Serializer
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SymfonySerializerAdapter implements Serializer
{
    /**
     * @var SymfonySerializer
     */
    private $target;

    /**
     * JmsSerializerAdapter constructor.
     *
     * @param SymfonySerializer $target
     */
    public function __construct(SymfonySerializer $target)
    {
        $this->target = $target;
    }

    /**
     * @param mixed                       $data any data
     * @param SerializerTypeDefinitionMap $definitionMap
     *
     * @return string
     */
    public function serialize($data, SerializerTypeDefinitionMap $definitionMap = null): string
    {
        return $this->target->serialize($data, 'json');
    }

    /**
     * Deserializes data into the given type.
     *
     * @param mixed                       $data
     * @param string                      $fqdn
     *
     * @param SerializerTypeDefinitionMap $definitionMap
     *
     * @return array|object
     *
     */
    public function deserialize($data, string $fqdn, SerializerTypeDefinitionMap $definitionMap = null)
    {
        return $this->target->deserialize($data, $fqdn, 'json');
    }
}
