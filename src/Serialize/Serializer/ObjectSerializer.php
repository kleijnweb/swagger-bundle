<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Request\ParameterCoercer;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;
use KleijnWeb\SwaggerBundle\Serialize\TypeResolver\ClassNameResolver;
use KleijnWeb\SwaggerBundle\Serialize\TypeResolver\SerializerTypeDefinitionMap;

/**
 * (De-) Serializes objects using JSON Schema
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ObjectSerializer implements Serializer
{
    /**
     * @param mixed                       $data
     * @param SerializerTypeDefinitionMap $definitionMap
     *
     * @return string
     */
    public function serialize($data, SerializerTypeDefinitionMap $definitionMap): string
    {
        $export = function ($item, \stdClass $schema) use (&$export) {
            if ($item instanceof \DateTimeInterface) {
                if ($schema->format == 'date') {
                    return $item->format('Y-m-d');
                }
                if ($schema->format == 'date-time') {
                    return $item->format(\DateTime::ISO8601);
                }
                throw new \UnexpectedValueException;
            }
            switch ($schema->type) {
                case 'array':
                    return array_map(function ($value) use (&$export, $schema) {
                        return $export($value, $schema->items);
                    }, $item);
                case 'object':
                    $class  = get_class($item);
                    $data   = (array)$item;
                    $offset = strlen($class) + 2;

                    $array = array_filter(array_combine(array_map(function ($k) use ($offset) {
                        return substr($k, $offset);
                    }, array_keys($data)), array_values($data)));

                    foreach ($array as $name => $value) {
                        $array[$name] = isset($schema->properties->$name)
                            ? $export($value, $schema->properties->$name)
                            : $value;
                    }

                    return $array;
                default:
                    if (!is_scalar($item)) {
                        throw new \UnexpectedValueException;
                    }

                    return $item;
            }

        };

        return json_encode($export($data, $definitionMap->getDefinitionByFqcn(get_class($data))));
    }

    /**
     * @param mixed                       $data
     * @param string                      $fqdn
     * @param SerializerTypeDefinitionMap $definitionMap
     *
     * @return mixed
     */
    public function deserialize($data, string $fqdn, SerializerTypeDefinitionMap $definitionMap)
    {
        $import = function ($item, \stdClass $schema) use (&$import) {
            switch ($schema->type) {
                case 'array':
                    return array_map(function ($value) use (&$import, $schema) {
                        return $import($value, $schema->items);
                    }, $item);
                case 'object':
                    $fqcn      = $schema->{'x-class'};
                    $object    = unserialize(sprintf('O:%d:"%s":0:{}', strlen($fqcn), $fqcn));
                    $reflector = new \ReflectionObject($object);

                    foreach ($item as $name => $value) {
                        if (!$reflector->hasProperty($name)) {
                            continue;
                        }
                        $value = isset($schema->properties->$name)
                            ? $import($value, $schema->properties->$name)
                            : $value;

                        $attribute = $reflector->getProperty($name);
                        $attribute->setAccessible(true);
                        $attribute->setValue($object, $value);
                    }

                    return $object;
                default:
                    return ParameterCoercer::coerceParameter($schema, $item);
            }
        };

        return $import(
            json_decode($data, true),
            $definitionMap->getDefinitionByFqcn($fqdn)
        );
    }
}
