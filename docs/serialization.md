# Serialization

## Object (De-) Serialization

By default Swagger bundle will simply encode and decode arrays. This means your controllers can expect `$request->getContent()`
 to contain an associative array, and are expected to return those as well.
 
Optionally SwaggerBundle can do object de- serialization. You will  need to pass the Symfony Components Serializer, 
or JMS\Serializer to the SerializerAdapter, which can be done by configuration:

```yml
swagger:
    serializer: 
        type: symfony
        namespace: My\Bundle\Resource\Namespace # Required for 'symfony' and 'jms' serializers
```

Replace `symfony` with `jms` to use the JMS Serializer. 

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
