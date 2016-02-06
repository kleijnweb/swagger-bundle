# KleijnWeb\SwaggerBundle 
[![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/swagger-bundle/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/swagger-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/swagger-bundle/v/stable)](https://packagist.org/packages/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig (except for dev purposes).

*SwaggerBundle only supports json in- and output, and only YAML Swagger defintions.*

Go to the [release page](https://github.com/kleijnweb/swagger-bundle/releases) to find details about the latest release.

__This bundle is currently actively maintained.__

For a pretty complete example, see [swagger-bundle-example](https://github.com/kleijnweb/swagger-bundle-example).

# This Bundle..

## Will:

 * Coerce parameters to their defined types when possible.
 * Validate content and parameters based on your Swagger spec.
 * Configure routing based on your Swagger spec. 
 * Handle standard status codes such as 500, 400 and 404.
 * Encode response data as JSON.
 * Return `application/vnd.error+json` responses when errors occur.
 * Utilize vnd.error's `logref` to make errors traceable.
 * Resolve JSON-Schema `$ref`s in your Swagger spec to allow reusable partial specs.
 
## Can:

 * (De-) Serialize objects using either the Symfony Component Serializer or JMS\Serializer

## Won't:

 * Handle Form posts.
 * Generate your API documentation. Use your Swagger document, plenty of options.
 * Mix well with GUI bundles. The bundle is biased towards lightweight API-only apps.
 * Work with JSON Swagger documents (yet, see [#10](https://github.com/kleijnweb/swagger-bundle/issues/10)).
 * Do content negotiation. May support XML in the future (low priority, see [#1](https://github.com/kleijnweb/swagger-bundle/issues/1)).

__TIP:__ Want to build an API-only app using this bundle? Try [kleijnweb/symfony-swagger-microservice-edition](https://github.com/kleijnweb/symfony-swagger-microservice-edition).

# Usage

1. Create a Swagger file, for example using http://editor.swagger.io/.
2. Install and configure this bundle 
3. Create one or more controllers (as services!), doing the actual work, whatever that may be.
4. You are DONE.

Pretty much. ;)

## Install And Configure

Install using composer (`composer require kleijnweb/swagger-bundle`). You want to check out the [release page](https://github.com/kleijnweb/swagger-bundle/releases) to ensure you are getting what you want and optionally verify your download.

Add Swagger-based routing to your app, for example:
 
```yml
test:
    resource: "config/yourapp.yml"
    type: swagger
```

The path here is relative to the `swagger.document.base_path` configuration option. The above example would require something like this in your config:

```yml
swagger:
    document: 
        base_path: "%kernel.root_dir%"
```

# Functional Details / Features

## Controllers

When a call is made that is satisfiable by SwaggerBundle, it uses Symfony Dependency Injection service keys to find the
delegation target of the request. It will assume the first segment in the Swagger paths is a resource name,
and looks for a service with the key `swagger.controller.%resource_name%`. The class method to be called by defaults corresponds
to the HTTP method name, but may be overridden by including `operationId` in your spec. You can also completely override this default by 
including an operationId referencing a DI key (using double colon notation, eg "my.controller.key:methodName"). 

Controller methods that expect content can either get the content from the `Request` object, or add a parameter named identical to the parameter with `in: body` set.
Any of these will work (assuming the `in: body` parameter is named `body` in your spec):

```php
public function placeOrder(Request $request)
{
    /** @var array $order */
    $order = $request->attributes->get('body');

   //...
}

public function placeOrder(Request $request)
{
    /** @var array $order */
    $order = $request->get('body');

   //...
}

public function placeOrder(array $body)
{
    //...
}
```

__NOTE:__ SwaggerBundle applies some type conversion to input and adds the converted types to the Request `attributes`. 
Using `Request::get()` will give precedence to parameters in `query`. These values will be 'raw', using `attributes` is preferred.

Your controllers do not need to implement any interfaces or extend any classes. A controller might look like this (using object deserialization, see section below):

```php
class StoreController
{
    /**
     * @param Order $body
     *
     * @return Order
     */
    public function placeOrder(Order $body)
    {
        return $body
            ->setId(rand())
            ->setStatus('placed');
    }
}
```

It would make more sense to name the parameter `order` instead of `body`, but this is how it is in the pet store example provided by Swagger.

Other parameters can be added to the signature as well, this is standard Symfony behaviour.

## Route Matching

To view the routes added by SwaggerBundle, you can use Symfony's `debug:router`. Route keys include the Swagger spec base filename to prevent collisions. For path parameters,
SwaggerBundle adds additional requirements to the routes. This way `/foo/{bar}` and `/foo/bar` wont conflict when `bar` is defined to be an integer. This also supports Swaggers `pattern`
and `enum` when dealing with string path parameters.

## Exception Handling

Any exceptions are caught, logged by the `@logger` service, and result in `application/vnd.error+json`. Routing failure results in a 404 response without `logref`.

## Input Validation

### Parameter Validation

SwaggerBundle will attempt to convert string values to any scalar value specified in your swagger file, within reason.
 For example, it will accept all of "0", "1", "TRUE", "FALSE", "true",  and "false" as boolean values, but wont blindly
 evaluate any string value as `TRUE`.
 
Parameter validation errors will result in a `vnd.error` response with a status code of 400.

__NOTE__: SwaggerBundle currently does not support `multi` for `collectionFormat` when handling array parameters.
 
### Content Validation

If the content cannot be decoded using the format specified by the request's Content-Type header, or if validation
of the content using the resource schema failed, SwaggerBundle will return a `vnd.error` response with a 400 status code.

### Object (De-) Serialization

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
When a controller action returns `NULL`, SwaggerBundle will return an empty `204` response.
  
## Authentication

SwaggerBundle 2.0+ does not include authentication functionality. The JWT support from 1.0 was moved into [kleijnweb/jwt-bundle](https://github.com/kleijnweb/jwt-bundle)).

When using `SecurityDefinition` type `oauth2`, it would be possible to translate *scopes* to Symfony roles, 
 add them to the user, and automatically configure `access_control`. 
 This is not currently implemented (yet, see [#15](https://github.com/kleijnweb/swagger-bundle/issues/15)).
 

# Developing

## Functional Testing Your API

The easiest way to create functional tests for your API, is by using mixin `ApiTestCase`. This will provide you with some convenience methods (`get()`, `post()`, `put()`, etc) and 
will validate responses using SwaggerAssertions to ensure the responses received are compliant with your Swagger spec. Example:

```php
class PetStoreApiTest extends WebTestCase
{
    use ApiTestCase;
    
    /**
     * Use config_basic.yml
     *
     * @var bool
     */
    protected $env = 'basic';

    /**
     * @see https://github.com/kleijnweb/swagger-bundle/issues/16
     *
     * @var bool
     */
    protected $validateErrorResponse = false;

    /**
     * Init response validation, point to your spec
     */
    public static function setUpBeforeClass()
    {
        static::initSchemaManager(__DIR__ . '/path/to/a/spec.yml');
    }

    /**
     * @test
     */
    public function placingAnOrderWillReturnDataWithOrderIsPlaced()
    {
        $content = [
            'petId'    => 987654321,
            'quantity' => 10,
        ];

        $actual = $this->post('/v2/store/order', $content);
        $this->assertSame('placed', $actual->status);
        $this->assertSame($content['petId'], $actual->petId);
        $this->assertSame($content['quantity'], $actual->quantity);
    }
}
```

When using ApiTestCase, initSchemaManager() will also validate your Swagger spec against the official schema to ensure it is valid.
 
# Utilities

See [swagger-bundle-tools](https://github.com/kleijnweb/swagger-bundle-tools))

## License

KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
