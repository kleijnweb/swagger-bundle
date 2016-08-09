# KleijnWeb\SwaggerBundle 
[![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/swagger-bundle/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/swagger-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/swagger-bundle/v/stable)](https://packagist.org/packages/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger ([Open API](https://openapis.org/)) specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig.

## Contract First

We say your OpenAPI definition *is* your config, and strive towards 'minimal additional config'. At the core, SwaggerBundle does three things:

 1. Configure Symfony Routing
 2. Validate input
 3. Coerce/transform in- and output

**HEADS UP:** _You are looking at the main (4.0 ALPHA) development line, which is PHP 7 only. SwaggerBundle 3.x is stable and works with PHP 5.4+._

## Usage

1. Create an OpenAPI document, for example using http://editor.swagger.io/.
2. Install and configure this bundle 
3. Create one or more controllers (as services)

## What's new in 4.0?

SwaggerBundle 4.0 is currently in the alpha stage. Much of the behavior dealing with OpenAPI documents has been moved to [KleijnWeb\PhpApi\Descriptions](https://github.com/kleijnweb/php-api-descriptions).

 - Serialization
 
Support for 3rd party serializers has been replaced by a new _API Description Based_ hydrator ([KleijnWeb\PhpApi\Hydrator](https://github.com/kleijnweb/php-api-hydrator)). Array serialization has been removed. The hydrator is optional, but without it in- and output will be `stdClass|stdClass[]`, not a combination of arrays and associative arrays as was the <4.0 default.

 - Testing
 
The dependency on `SwaggerAssertions` has been removed, as response validation is now facilitated by `KleijnWeb\PhpApi\Descriptions`.

 - Errors
 
`vnd.error` support has been removed in favor of simpler error responses. This also gets rid of some dependencies that were unneeded for most use cases.

## <a name="topics"></a> Documentation Topics

 - [Install And Configure](#config)
 - [Routing](#routing)
 - [Validation](#validation)
 - [Controllers](#controllers)
 - [Errors](#errors)
 - [Serialization](#serialization)
 - [Responses](#responses)
 - [Caching](#caching)
 - [Functional Testing](#testing)
 - [Contributing](#contributing)
 
## <a name="config"></a> Install And Configure

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

### Bundle Config

You can dump an up-to-date and documented configuration reference using `config:dump-reference` after you've added the bundle to your `AppKernel`.

## <a name="routing"></a> Routing

### Route Matching

To view the routes added by SwaggerBundle, you can use Symfony's `debug:router`. Route keys include the Swagger spec base filename to prevent collisions. For path parameters,
SwaggerBundle adds additional requirements to the routes. This way `/foo/{bar}` and `/foo/bar` wont conflict when `bar` is defined to be an integer. 
This also supports Swaggers `pattern` and `enum` when dealing with string path parameters.

### Controller Resolution

All controllers must be defined as services in the DI container. SwaggerBundle sees an `operation id` as composed from the following parts:

```
[router].[controller]:[method]
```

`Router` is a DI key namespace in this context. The `router` segment defaults to `swagger.controller`, but can be overwritten at the `Path Object` level using `x-router`:

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    ...
```

The `controller` segments defaults to the resource name as extracted from the path by convention. For example, for path `/foo/something` the default router + controller would be: `swagger.controller.foo`.

You can override the whole of `[router].[controller]` using `x-router-controller`. This will not only override the default, but any declaration of `x-router`, too:

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    ...
```

The following is also supported (set controller for a specific method):

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    patch:
      x-router-controller: an.alternate.di.namespace.controller
    ...
```

Finally, the `method` segment defaults to the HTTP method name, but may be overridden using Swagger's `operationId` or `x-router-controller-method`. Note the Swagger spec requires `operationId` to be unique, so while `operationId` can contain only the method name, you're usually better off using `x-router-controller-method`.
You can also use a fully quantified operation id using double colon notation, eg "my.controller.namespace.myresource:methodName". Combining `x-router` or `x-router-controller` and a quantified `operationId` ignores the former.

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    post:
      # Ingores declarations above
      operationId: my.controller.namespace.myresource:methodName
    ...
```

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    post:
      # Resolves to 'my.default.controller.namespace.foo:methodName'
      x-router-controller-method: methodName
    ...
```

```yaml
paths:
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    post:
      # Same as above. Valid, but discouraged
      operationId: methodName
    ...
```

[Back to topics](#topics)

## <a name="validation"></a> Validation

### Parameter Validation

SwaggerBundle will attempt to convert path and query string values to the scalar value specified in your swagger file, within reason.
 For example, it will accept all of "0", "1", "TRUE", "FALSE", "true",  and "false" as boolean values, but wont blindly
 evaluate any string value as `TRUE`. When a parameter can not be sensibly coerced into its specified type, the value is left as is and will likely fail validation.
 
__NOTE__: SwaggerBundle currently does not support `multi` for `collectionFormat` when handling array parameters (see [#50](https://github.com/kleijnweb/swagger-bundle/issues/50)).
 
### Content Validation

If the content cannot be decoded as JSON, or if validation of the content using the resource schema failed, 
SwaggerBundle will return an exception response with a 400 status code.

[Back to topics](#topics)

## <a name="controllers"></a> Controllers

Your controllers do not need to implement any interfaces or extend any classes. SwaggerBundle modifies the Symfony request `attributes`, which by default are mapped to controller method arguments. Just match the name and type from your API description.

```php
class StoreController
{
    public function updateOrder(int $id, Order $order)
    {
        return $body
            ->setId(rand())
            ->setStatus('placed');
    }
}
```
While it is possible to have the full `Request` object injected, this is discouraged.

[Back to topics](#topics)

## <a name="errors"></a> Errors

Bastardized `vnd.error` for simplicity. Will include a message and a `logref`. The produced validation errors (if applicable) are included as a simple array of strings.

```json
{
  "message": "Validation failed",
  "logref": "789gvtyuu",
  "errors": ["The property foo.bar is required"]
}
```

[Back to topics](#topics)

## <a name="serialization"></a> Serialization

SwaggerBundle will *only* (de-) serialize JSON, outputting `stdClass|stdClass[]`.

## Object Hydration

Optionally SwaggerBundle can use [KleijnWeb\PhpApi\Hydrator](https://github.com/kleijnweb/php-api-hydrator):

```yml
swagger:
  hydrator: 
    namespaces: [My\Bundle\Resource\Namespace]
```

[Back to topics](#topics)

## <a name="serialization"></a> Responses
 
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

[Back to topics](#topics)

## <a name="caching"></a> Caching

### Caching Descriptions

To configure caching for [KleijnWeb\PhpApi\Descriptions](https://github.com/kleijnweb/php-api-descriptions):

```yml
swagger:
  document: 
    cache: "some.doctrine.cache.service"
```

### HTTP Caching

SwaggerBundle works well with [E-Tags Based on REST Semantics](https://github.com/kleijnweb/rest-e-tag-bundle).
 
[Back to topics](#topics)

## <a name="testing"></a> Functional Testing

The easiest way to create functional tests for your API, is by using mixin `ApiTestCase`. This will provide you with some convenience methods (`get()`, `post()`, `put()`, etc) and 
will validate responses to ensure the responses received are compliant with your OpenAPI document.

```php
class PetStoreApiTest extends WebTestCase
{
    use ApiTestCase;
    
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

# Contributing

Pull request are *very* welcome, as long as:

 - All automated checks were successful
 - Backwards compatibility is maintained
 - When applicable, the relevant documentation is updated
 
[Back to topics](#topics)

## FAQ

 - Will SwaggerBundle do `x`?
 
If `x` is any of these, the answer will probably stay 'no':

 * Handle Form posts.
 * Generate API documentation.
 * Mix well with GUI bundles. The bundle is biased towards lightweight API-only apps.
 * Support Symfony sub-requests. You won't miss them.
 * Support XML.
 
## Notes

This bundle is currently actively maintained. Go to the [release page](https://github.com/kleijnweb/swagger-bundle/releases) to find details about the latest release.

## License

KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
