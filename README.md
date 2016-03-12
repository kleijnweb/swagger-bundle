# KleijnWeb\SwaggerBundle 
[![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/swagger-bundle/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/swagger-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/swagger-bundle/v/stable)](https://packagist.org/packages/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger ([Open API](https://openapis.org/)) specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig.

## Important Notes
 * SwaggerBundle only supports json in- and output
 * This bundle is currently actively maintained.
 * Go to the [release page](https://github.com/kleijnweb/swagger-bundle/releases) to find details about the latest release.

For a pretty complete example, see [swagger-bundle-example](https://github.com/kleijnweb/swagger-bundle-example).
A minimal example is also [available](https://github.com/kleijnweb/symfony-swagger-microservice-edition).

## This bundle will..

 * Handle both JSON and YAML swagger specs transparently.
 * Configure routing based on your Swagger documents(s), accounting for things like type, enums and pattern matches. 
 * Validate body and parameters based on your Swagger documents(s).
 * Coerce query and path parameters to their defined types when possible.
 * Resolve [JSON Pointer](http://json-spec.readthedocs.org/en/latest/pointer.html)s anywhere in your Swagger documents and partials (not just in the [JSON Schema](http://json-schema.org/) bits).
 * Return [vnd.error](https://github.com/blongden/vnd.error) (`application/vnd.error+json`) responses when errors occur.
 * Utilize vnd.error's `logref` to make errors traceable.
 * Optionally (De-) Serialize objects using either the Symfony Component Serializer or JMS\Serializer

## It won't, and probably never will:

 * Handle Form posts.
 * Generate your API documentation. Use your Swagger documents, plenty of options.
 * Mix well with GUI bundles. The bundle is biased towards lightweight API-only apps.
 * Do content negotiation or support XML.

# Usage

1. Create a Swagger file, for example using http://editor.swagger.io/.
2. Install and configure this bundle 
3. Create one or more controllers (as services), doing the actual work, whatever that may be.

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

### Controller Resolution

All controllers must be defined as services in the DI container.

SwaggerBundle sees an `operation id` as composed from the following parts:

```
[router][controller]:[method]
```

The `router` segment defaults to `swagger.controller`, but _can_ be overwritten at the `Path Object` level using `x-router`:

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    ..
```

The `controller` segments defaults to the resource name as extracted from the path by convention. For example, for path `/foo/something` the default router + controller would be: `swagger.controller.foo`.

Finally, the `method` segment defaults to the HTTP method name, but may be overridden using Swagger's `operationId`. It is possible to use only the method name here, but note the Swagger spec requires `operationId` to be unique.
You can also use a fully quantified operation id using double colon notation, eg "my.controller.namespace.myresource:methodName". Combining `x-router` and a quantified `operationId` currently ignores the former.

### Controller Conventions

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

__NOTE:__ SwaggerBundle applies some type conversion to query and path parameters and adds the converted values to the Request `attributes`. 
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

All (type-casted) parameters can be added to the signature, since they are attributes when the controller is invoked. This is standard Symfony behaviour.

## Caching

Parsing YAML and resolving JSON Pointers can be slow, especially with larger specs with external references. SwaggerBundle can use a Doctrine cache to mitigate this. Use a DI key to reference the service you want to use:

```yml
swagger:
  document: 
    cache: "some.doctrine.cache.service"
```

## Route Matching

To view the routes added by SwaggerBundle, you can use Symfony's `debug:router`. Route keys include the Swagger spec base filename to prevent collisions. For path parameters,
SwaggerBundle adds additional requirements to the routes. This way `/foo/{bar}` and `/foo/bar` wont conflict when `bar` is defined to be an integer. This also supports Swaggers `pattern`
and `enum` when dealing with string path parameters.

## Exception Handling

Any exceptions are caught, logged by the `@logger` service, and result in `application/vnd.error+json`. The log-level/severity depends on the exception type and/or code. "Not Found" errors are logged as 'INFO'.  

## Input Validation

### Parameter Validation

SwaggerBundle will attempt to convert path and query string values to the scalar value specified in your swagger file, within reason.
 For example, it will accept all of "0", "1", "TRUE", "FALSE", "true",  and "false" as boolean values, but wont blindly
 evaluate any string value as `TRUE`. When a parameter can not be sensibly coerced into its specified type, the value is left as is and will likely fail validation.
 
__NOTE__: SwaggerBundle currently does not support `multi` for `collectionFormat` when handling array parameters (see [#50](https://github.com/kleijnweb/swagger-bundle/issues/50)).
 
### Content Validation

If the content cannot be decoded as JSON, or if validation of the content using the resource schema failed, 
SwaggerBundle will return a `vnd.error` response with a 400 status code.

### Validation Feedback

Parameter validation errors will result in a `vnd.error` response with a status code of 400.

The validation errors (produced by [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema)), are included in the response, with [HAL](http://stateless.co/hal_specification.html) links that are essentially JSON Pointers
to the parameter definition in the relevant Swagger Document.

In order for this to work properly, you may need some additional config.

When SwaggerBundle generates the JSON Pointer URI, it uses the following conventions:

1. For the protocol/scheme, it uses to the scheme used to make the request, unless globally configured otherwise, *or* if not in the specs `schemes` (in which case it will use, in order of preference: https, wss, http, ws).
2. For the host name, it will prefer the global config. If not defined it will use the value of `host` in the spec, ultimately falling back to the host name used to make the request.
3. For the relative path, it will use the path relative to `swagger.document.base_path`. If configured, it will prefix the `swagger.document.public.base_url`

Example:

```yaml
swagger:
  document:
    public:
      scheme: 'http' # Even if the spec claims it support https, this will cause the links to use http, unless the request was made using https (likewise you can use this to force https even if the request was made using http)
      base_url: specs # This will prefix '/specs' to all paths
      host: some.host.tld # Fetch specs from said host, instead of what's defined in the spec or the current one
```

Example validation failure response:

```json
{
    "message": "Input Validation Failure",
    "logref": "56c083be3f9de",
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/v2\/user\/login"
        },
        "about": {
            "href": "http:\/\/petstore.swagger.io\/swagger\/petstore.yml",
            "title": "Api Specification"
        }
    },
    "_embedded": {
        "errors": [
            {
                "message": "the property username is required",
                "path": "\/paths\/~1user~1login\/get\/x-request-schema\/properties\/username",
                "_links": {
                    "self": {
                        "href": "http:\/\/petstore.swagger.io\/swagger\/petstore.yml#\/paths\/~1user~1login\/get\/parameters\/0"
                    }
                }
            },
            {
                "message": "the property password is required",
                "path": "\/paths\/~1user~1login\/get\/x-request-schema\/properties\/password",
                "_links": {
                    "self": {
                        "href": "http:\/\/petstore.swagger.io\/swagger\/petstore.yml#\/paths\/~1user~1login\/get\/parameters\/1"
                    }
                }
            }
        ]
    }
}
```
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

### The HTTP Response
 
When a controller action returns `NULL`, SwaggerBundle will return an empty `204` response, provided that one is defined in the specification.
Otherwise, it will default to the first 2xx type response defined in your spec, or if all else fails, simply 200.

You cannot return Symfony responses from your controllers. Any response manipulation (including custom status codes) you want needs to be implemented using "Response Listeners". Example that sets some headers:

```yaml
services:
  your_response_listener:
    class: Some\Namespace\ResponseListener
    tags:
      - { name: kernel.event_listener, event: kernel.response, method: onKernelResponse }
```
```php
class ResponseListener
{
    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        $headers = $event->getResponse()->headers;
        switch ($request->attributes->get('_swagger_path')) {
            case '/user/login':
                $headers->set('X-Rate-Limit', 123456789);
                $headers->set('X-Expires-After', date('Y-m-d\TH:i:s\Z'));
                break;
            default:
                //noop
        }
    }
}

```


# Developing

# Utilities

See [swagger-bundle-tools](https://github.com/kleijnweb/swagger-bundle-tools).

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
 
## License

KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
