<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Serialize\SerializationTypeResolver;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;

/**
 * (De-) Serializes objects using JSON Schema
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerSerializer implements Serializer
{
    /**
     * @var SerializationTypeResolver
     */
    private $serializationTypeResolver;

    /**
     * @var \stdClass
     */
    private $definitions;

    /**
     * SwaggerSerializer constructor.
     *
     * @param SerializationTypeResolver $serializationTypeResolver
     * @param \stdClass                 $definitions
     */
    public function __construct(SerializationTypeResolver $serializationTypeResolver, \stdClass $definitions)
    {
        $this->serializationTypeResolver = $serializationTypeResolver;
        $this->definitions               = $definitions;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public function serialize($data): string
    {
        $export = function ($object) use (&$export) {
            if (!is_object($object)) {
                return $object;
            }
            $class  = get_class($object);
            $data   = (array)$object;
            $offset = strlen($class) + 2;
            $keys   = array_map(function ($k) use ($offset) {
                return substr($k, $offset);
            }, array_keys($data));

            return array_map(function ($item) use ($offset, &$export) {
                return is_object($item) ? $export($item) : (is_array($item) ? array_map($export, $item) : $item);
            }, array_combine($keys, array_values($data)));
        };

        return json_encode($export($data));
    }

    /**
     * @param mixed  $data
     * @param string $type
     *
     * @return mixed
     */
    public function deserialize($data, string $type)
    {
        $import = function ($item, \stdClass $schema) use (&$import) {
            switch ($schema->type) {
                case 'array':
                    return array_map(function ($value) use (&$import, $schema) {
                        return $import($value, $schema->items);
                    }, $item);
                case 'object':
                    $fqcn      = $this->serializationTypeResolver->resolveUsingSchema($schema);
                    $object    = unserialize(sprintf('O:%d:"%s":0:{}', strlen($fqcn), $fqcn));
                    $reflector = new \ReflectionObject($object);

                    foreach ($item as $name => $value) {
                        $value = isset($schema->properties->$name)
                            ? $import($value, $schema->properties->$name)
                            : $value;

                        $attribute = $reflector->getProperty($name);
                        $attribute->setAccessible(true);
                        $attribute->setValue($object, $value);
                    }

                    return $object;
                default:
                    settype($item, $schema->type);

                    return $item;
            }
        };

        return $import(
            json_decode($data, true),
            $this->definitions->{$this->serializationTypeResolver->reverseLookup($type)}
        );
    }
}
