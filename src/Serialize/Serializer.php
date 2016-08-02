<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Serialize\TypeResolver\SerializerTypeDefinitionMap;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface Serializer
{
    /**
     * @param mixed                       $data
     * @param SerializerTypeDefinitionMap $definitionMap
     *
     * @return string
     */
    public function serialize($data, SerializerTypeDefinitionMap $definitionMap): string;

    /**
     * @param mixed                       $data
     * @param string                      $fqdn
     * @param SerializerTypeDefinitionMap $definitionMap
     *
     * @return mixed
     */
    public function deserialize($data, string $fqdn, SerializerTypeDefinitionMap $definitionMap);
}
