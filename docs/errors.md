# Exceptions

SwaggerBundle supports these exception handling strategies:

| Config option | Description |
|--|--|
| simple (_default_) | Creates a simple `application/json` error response with a logref with basic validation feedback when applicable. | 
| vnd_error | Creates an `application/vnd.error+json` with JSON pointers to relevant specification elements when applicable. | 
| _\<custom response>_ | Use the default exception listener, but with your own custom `ErrorResponseFactory`. The configuration value start with an "@". | 
| fallthrough | Do not catch any exceptions. Useful for debugging of when you want to register your own `kernel.exception` listener. |

### Simple Error Feedback (Default)

Bastardized `vnd.error` for simplicity. Will include a message and a `logref`. The produced validation errors are included as a simple array of strings. Example:

```json
{
  "message": "Validation failed",
  "logref": "789gvtyuu",
  "errors": ["The property foo.bar is required"]
}
```

### Vnd.error Validation Feedback with JSON Pointers 

Parameter validation errors will result in a `vnd.error` response with a status code of 400. The validation errors are included in the response, with [HAL](http://stateless.co/hal_specification.html) links that are JSON Pointers
to the parameter definition in the relevant Swagger Document.

In order for this to work properly, you may need some additional config.

When SwaggerBundle generates the JSON Pointer URI, it uses the following conventions:

1. For the protocol/scheme, it uses to the scheme used to make the request, unless globally configured otherwise, *or* if not in the specs `schemes` (in which case it will use, in order of preference: https, wss, http, ws - the ws variants do not seem to be supported by Symfony though).
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
      "href": "http://localhost/v2/user/login"
    },
    "about": {
      "href": "http://petstore.swagger.io/swagger/petstore.yml",
      "title": "Api Specification"
    }
  },
  "_embedded": {
    "errors": [
      {
        "message": "the property username is required",
        "path": "/paths/~1user~1login/get/x-request-schema/properties/username",
        "_links": {
          "self": {
            "href": "http://petstore.swagger.io/swagger/petstore.yml#/paths/~1user~1login/get/parameters/0"
          }
        }
      },
      {
        "message": "the property password is required",
        "path": "/paths/~1user~1login/get/x-request-schema/properties/password",
        "_links": {
          "self": {
            "href": "http://petstore.swagger.io/swagger/petstore.yml#/paths/~1user~1login/get/parameters/1"
          }
        }
      }
    ]
  }
}
```

### Custom Response

An example of a custom error response builder:

```yaml
services:
  my_namespace.error_response_factory:
    class: MyNamespace\CustomErrorResponseFactory
```

```yaml
swagger:
  errors: 
    strategy: '@my_namespace.error_response_factory'
```

```php
namspace MyNamespace;

use KleijnWeb\SwaggerBundle\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SimpleErrorResponseFactory implements ErrorResponseFactory
{
    public function create(HttpError $error): Response
    {
        return new JsonResponse(['message' => 'The server made a booboo'], $error->getStatusCode());
    }
}
```

### Logrefs

By default this simply uses `uniqid()`, but you can refer to a custom reference string builder class by DI key:
 
 ```yaml
 services:
   my_namespace.logref_builder:
     class: MyNamespace\CustomLogRefBuilder
 ```
 
 ```yaml
 swagger:
   errors: 
     logref_builder: '@my_namespace.logref_builder'
 ```
 
 ```php
 namspace MyNamespace;
 
 use KleijnWeb\SwaggerBundle\Response\Error\LogRefBuilder;
 use Symfony\Component\HttpFoundation\Request;
 
 class CustomLogRefBuilder implements LogRefBuilder
 {
     public function create(Request $request, \Exception $exception): string
     {
         return uniqid("{$request->headers->get('x-request-id')}_{$exception->getCode()}_");
     }
 }
 ```