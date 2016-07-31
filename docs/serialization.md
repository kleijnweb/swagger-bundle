# Serialization

## Object (De-) Serialization

By default Swagger bundle will simply encode and decode arrays. This means your controllers can expect `$request->getContent()`
 to contain an associative array, and are expected to return those as well.
 
Optionally SwaggerBundle can do object de- serialization. You will need to pass the Symfony Components Serializer, 
or JMS\Serializer to the SerializerAdapter, which can be done by configuration:

```yml
swagger:
    serializer: 
        type: symfony
        namespace: My\Bundle\Resource\Namespace # Required for 'symfony' and 'jms' serializers
```

Replace `symfony` with `jms` to use the JMS Serializer. You can specifiy more than one namespace by replacing the string value for `namespace` with an array which will be tried in order of occurrence.

__NOTE:__ You do not need to install `JMSSerializerBundle`. Just `composer require jms/serializer` (or `composer require symfony/serializer`).

The `namespace` value is used to configure `@swagger.serializer.type_resolver` (`SerializationTypeResolver`).

`SerializationTypeResolver` will use the last segment of the `$ref` (or `id`) of the schema for the `in: body` parameter.
  Eg `#/definitions/Pet` will resolve to `My\Bundle\Resource\Namespace\Pet`. Currently `SerializationTypeResolver` supports only a single namespace.
  
This will only work for operations where the `in: body` parameter is defined, for example:

```yml
parameters:
  - in: body
    name: body
    description: Pet object that needs to be added to the store
    required: false
    schema:
      $ref: '#/definitions/Pet'
```

Similar to arrays, you may use the reference the parameter in your controller signature, or use `attributes`:


```php
public function placeOrder(Order $body)
{
    //...
}
```
```php
public function placeOrder(Request $request)
{
    /** @var Order $order */
    $order = $request->attributes->get('body');

   //...
}
```

### Custom Serializers

Below is an early prototype of the SwaggerSerializer which is a good example to create custom serializers.

```yaml
services:
  my_namespace.serializer:
    class: MyNamespace\SwaggerSerializer
```

```yaml
swagger:
  serializer: 
    type: '@my_namespace.serializer'
    namespace: My\Bundle\Resource\Namespace
```

```php
namspace MyNamespace;

class SwaggerSerializer implements Serializer
{
    private $serializationTypeResolver;
    private $definitions;

    public function __construct(SerializationTypeResolver $serializationTypeResolver, \stdClass $definitions)
    {
        $this->serializationTypeResolver = $serializationTypeResolver;
        $this->definitions               = $definitions;
    }

    public function serialize($data): string
    {
        $export = function ($object) use (&$export) {
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

        return $import(json_decode($data, $this->definitions->$type), true);
    }
}
```
