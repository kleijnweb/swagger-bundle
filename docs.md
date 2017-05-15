# <a name="topics"></a> Topics

 - [Install And Configure](#config)
 - [Routing](#routing)
 - [Validation](#validation)
 - [Security](#security)
 - [Controllers](#controllers)
 - [Errors](#errors)
 - [Serialization](#serialization)
 - [Responses](#responses)
 - [Caching](#caching)
 - [Testing](#testing)
 - [Contributing](#contributing)
 
---------------------------------------
 
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

NOTE: It is possible to use network URIs here, but the results may be cached across requests depending on your settings.

### Bundle Config

You can dump an up-to-date and documented configuration reference using `config:dump-reference` after you've added the bundle to your `AppKernel`.

---------------------------------------

## <a name="routing"></a> Routing

For details, check out [kleijnweb/php-api-routing-bundle](https://github.com/kleijnweb/php-api-routing-bundle) (SwaggerBundle is configured to work only with `swagger` instead of `php-api`). 

[Back to topics](#topics)

---------------------------------------

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

---------------------------------------

## <a name="security"></a> Security

### WARNING: 

Using OpenAPI documents to configure security dynamically is a potential security risk. Take care that your source documents and caches (`swagger.document.cache`) are secure. 

### Firewall Request Matching

You can of course use standard path based matching, but a more flexible way is to use the bundled request matcher. You can use it as directly as a firewall request matcher, or in a listener to wrap to authenticator you want to use (see subsection).

```yml
security:
  firewalls:
    firewall_name:
      request_matcher: swagger.security.request_matcher
      #...
```

By default, the matcher will return TRUE if the request was routed by SwaggerBundle *and* the target operation has a security segment defined. Optionally you can configure SwaggerBundle to match any request routed by SwaggerBundle:

```yml
 swagger:
   security:
     match_unsecured: true
```


### Request Authorization

As an alternative to manually configuring URI based role access (`access_control`), you can use the `RequestAuthorizationListener`. This performs a function similar to the `AccessListener` in the firewall, but instead of a list of roles to test the authentication against, voters are passed the `Request` object. 

This security listener is not enabled by default, to enable:

```yml
 security:
  firewalls:
    firewall_name:
      swagger: { request_voting: true }
```

**NOTE:** The "request voting" takes place before anonymous authentication and enforcing of `access_control` rules.

#### Authorization Of Anonymous Authenticated Users

It is currently not possible to use `anonymous` and `swagger.request_voting` on the same firewall. If you require anonymous access to some of your operations, you can do the following:

1. Make sure `match_unsecured` is FALSE (or omitted, since that is the default)
2. Add a fallback firewall that allows anonymous access

Example:

```yml
swagger:
  security:
    match_unsecured: false
     
security:
  firewalls:
    firewall_name:
      request_matcher: swagger.security.request_matcher
      swagger: { request_voting: true }
    fallback:
      anonymous: ~
```

To prevent exposure of operations by unintentionally omitting `security` in your OpenAPI document, you may want to be explicit:

```yml
security:
  firewalls:
    firewall_name:
      request_matcher: swagger.security.request_matcher
      swagger: { request_voting: true }
    fallback:
      patters: '^/v1/allows-anon'
      anonymous: ~
      
access_control:
  - { path: '/', roles: 'IS_AUTHENTICATED_FULLY' }
  - { path: '/v1/allows-anon', roles: 'IS_AUTHENTICATED_ANONYMOUSLY' }
```
 
### Role Based Access (RBAC)

The bundled `RbacRequestVoter` inspects the OpenAPI document for security info and uses the standard `security.access.decision_manager`. This means you can still use `role_hierarchy`.

If the target operation has a `security` section, it will require the role `IS_AUTHENTICATED_FULLY`, if not, `IS_AUTHENTICATED_ANONYMOUSLY`. In addition, you can use the `x-rbac` OperationObject extension:

```yml
paths:
  /some-path:
    get:
      x-rbac: ['group1', 'group2']
```

Group names are normalized to Symfony convention (upper case and prefixed with `ROLE_`).


To enable OpenAPI based RBAC:

```yml
security:
  firewalls:
    firewall_name:
      swagger: { request_voting: true , rbac: true }
```

### Custom Request Authorization Voters

Creating custom voters is covered in the [Symfony docs](http://symfony.com/doc/current/security/voters.html). The votes should respond to the attibute `RequestAuthorizationListener::ATTRIBUTE` and are passed a `Request` object.

---------------------------------------

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

---------------------------------------

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

---------------------------------------

## <a name="serialization"></a> Responses
 
When a controller action returns `NULL` or an empty string, SwaggerBundle will return an empty `204` response, provided that one is defined in the specification.
Otherwise, it will default to the first 2xx type response defined in your spec, or if all else fails, simply 200.

This behavior is defined by `KleijnWeb\PhpApi\Middleware\Util\OkStatusResolver`. You can override it by subclassing it and injecting it into the `ResponseFactory`:

```yaml
swagger:
  ok_status_resolver: "my.custom.resolver"
```

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
        switch ($request->attributes->get(RequestMeta::ATTRIBUTE)) {
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

---------------------------------------

## <a name="caching"></a> Caching

### Caching Descriptions

To configure caching for [KleijnWeb\PhpApi\Descriptions](https://github.com/kleijnweb/php-api-descriptions):

```yml
swagger:
  document: 
    cache: "some.doctrine.cache.service"
```

[Back to topics](#topics)

### HTTP Caching

SwaggerBundle works well with [E-Tags Based on REST Semantics](https://github.com/kleijnweb/rest-e-tag-bundle).
 
[Back to topics](#topics)

---------------------------------------

## <a name="testing"></a> Testing

The easiest way to create functional tests for your API, is by using mixin `ApiTestCase`. This will provide you with some convenience methods (`get()`, `post()`, `put()`, etc), and convert validation errors into readable exception messages.

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

**NOTE:** If you implement `setUp()` you will have to manually invoke `createApiTestClient()`. 

### Response validation

During any type of testing, you will want to have `validate_responses` option enabled. This will ensure the responses produced by your controllers are complient with your OpenAPI document.

[Back to topics](#topics)
