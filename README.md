# KleijnWeb\SwaggerBundle [![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig (except for dev purposes).

*SwaggerBundle only supports json in- and ouput, and only YAML Swagger defintions.*

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

 * Integrate OAuth 2.0 compatible JWT API tokens for authentication
 * Amend your Swagger spec to include the error responses added by SwaggerBundle.
 * (De-) Serialize objects using either the Symfony Component Serializer or JMS\Serializer
 * Generate DTO-like classes representing resources in your Swagger spec.

## Won't:

 * Handle Form posts.
 * Generate your API documentation. Use your Swagger document, plenty of options.
 * Mix well with GUI bundles. The bundle is biased towards lightweight API-only apps.
 * Update the resource schemas in your Swagger spec when these classes change (not yet, but __soon__, see [#3](https://github.com/kleijnweb/swagger-bundle/issues/3)).
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

Install using composer (`composer require kleijnweb/swagger-bundle`). Add Swagger-based routing to your app, for example:
 
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
to the HTTP method name, but may be overridden by including `operationId` in your spec. Controller methods that expect content can either
 get the content from the `Request` object, or add a parameter named identical to the parameter with `in: body` set:
 
Any of these will work (assuming the `in: body` parameter is named `body` in your spec):

```php
public function placeOrder(Request $request)
{
    /** @var array $order */
    $order = $request->getContent();

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

## Exception Handling

Any exceptions are caught, logged by the `@logger` service, and result in `application/vnd.error+json`. Routing failure results in an empty 404 response.

## Input Validation

### Parameter Validation

SwaggerBundle will attempt to convert string values to any scalar value specified in your swagger file, within reason.
 For example, it will accept all of "0", "1", "TRUE", "FALSE", "true",  and "false" as boolean values, but wont blindly
 evaluate any string value as `TRUE`.
 
Parameter validation errors will result in a `vnd.error` response with a status code of 400.

__NOTE__: SwaggerBundle currently does not support `multi` for `collectionFormat` when handling array parameters.
 
### Content Validation

If the content cannot be deserialized using the format specified by the request's Content-Type header, or if validation
of the content using the resource schema failed, SwaggerBundle will return a `vnd.error` response with a 400 status code.

### Object (De-) Serialization

By default Swagger bundle will only serialize and deserialize arrays. This means your controllers can expect `$request->getContent()`
 to contain an associative array, and are expected to return those as well.
 
Optionally SwaggerBundle can do object de- serialization. Just add the following  

You'll need to pass the Symfony Components Serializer or JMS\Serializer to the SerializerAdapter, which can be done by configuration:

```yml
swagger:
    serializer: 
        type: symfony
        namespace: My\Bundle\Resource\Namespace
```

Replace `symfony` with `jms` to use the JMS Serializer. 

__NOTE:__ You do not need to install `JMSSerializerBundle`. Just `composer require jms/serializer` (or `composer require symfony/serializer`).

The `namespace` value is used to configure `@swagger.serializer.type_resolver`.

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

The `ResponseFactory` will try to deserialize any objects of a class other than `\stdClass`. 

Similar to arrays, you may use the reference the parameter in your controller signature, or use `$request->getContent()`:

```php
public function placeOrder(Request $request)
{
    /** @var Order $order */
    $order = $request->getContent();

   //...
}
```

```php
public function placeOrder(Order $body)
{
    //...
}
```
  
#### Using Annotations

In order to use annotations, you should make sure you use an autoload bootstrap
 that will initialize doctrine/annotations:
 
```php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
```

Good chance you are already using a bootstrap file like this, but if the annotations won't load, this is where to look.

## Authentication

SwaggerBundle comes with an optional `JwtAuthenticator` which implements a OAuth 2 compatible token-based authentication method. 
The role of the server with SwaggerBundle in OAuth terms is "Resource Server" (ie your app has some resources belonging to the "Resource Owner" that a client program wants access to). 

The token is validated using standard (reserved) JWT claims:

| Name  | Type | Description |
|-------|---------|-------|
| `exp` | int [1] | Expiration time must be omitted [3] or be smaller than `time() + leeway` [2]. |
| `nbf` | int [1] | "Not before", token validity start time, must be omitted [3] or greater than or equal to `time() - leeway` [2]. |
| `iat` | int [1] | The time the token was issued, must be omitted [3] or smaller than configured `minIssueTime + leeway`. Required when `minIssueTime` configured.  |
| `iss` | string | Issuer of the token, must match configured `issuer`. Required when `issuer` configured. |
| `aud` | string | JWT "audience", must be omitted [3] or match configured `audience` if configured. Required when `audience` configured. |
| `prn` | string | JWT "principal". Used as `username` for Symfony Security integration. Always required, without it the "Resource Owner cannot be identified. |
| `jti` | string | JWT "ID". Not used, will be ignored. |
| `typ` | string | Not used, will be ignored. |
 
 - [1] Unix time
 - [2] The `leeway` allows a difference in seconds between the issuer of the token and the server running your app with SwaggerBundle. Keep at a low number, defaults to 0.
 - [3] Mark any claim required, including custom (non-reserved) ones, using the `require` configuration option.
 
All other claims encountered are ignored. 

### Keys

`JwtAuthenticator` supports multiple keys, and allows all options to be configured per `kid` (key ID, which must be included in the JWT header when more than 1 key is configured):

```yml
swagger:
    auth: 
       keys:
          keyOne: # Only one key, 'kid' is optional (but must match when provided)
            issuer: http://api.server.com/oauth2/token # OAuth2 example, but could be any string value
            audience: ~ # NULL, accept any
            minIssueTime: 1442132949 # Reject 'old' tokens, regardless of 'exp'
            require: [nbf, exp, my-claim] # Mark claims as required
            leeway: 5 # Allow 5 seconds of time de-synchronization between this server and api.server.com
    
```

SwaggerBundle and the issuer must share a secret in order for SwaggerBundle to be able to verify tokens. You can choose between a *pre shared key* (PSK) or *asymmetric keys*. 

```yml
swagger:
    auth:
       keys:
          keyOne: # Must match 'kid'
            issuer: http://api.server1.com/oauth2/token
            secret: 'A Pre-Shared Key'
            type: ~ # Defaults to HS256 (HMACSHA256). All options: HS256, HS512, RS256 and RS512
          keyTwo: # Must match 'kid'
            issuer: http://api.server2.com/oauth2/token
            type: RS256 # RSA SHA256, needed for asymmetric keys
            secret: |
                    -----BEGIN PUBLIC KEY-----
                    MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwND1VMVJ3BC/aM38tQRH
                    2GDHecXE8EsGoeAeBR5dFt3QC1/Eoub/F2kee3RBtI6I+kDBjrSDz5lsqh3Sm7N/
                    47fTKZLvdBaHbCuYXVBQ2tZeEiUBESnsY2HUzXDlqSyDWohuiYeeL6gewxe1CnSE
                    0l8gYZ0Tx4ViPFYulva6siew0f4tBuSEwSPiKZQnGcssQYJ/VevTD6L4wGoDhkXV
                    VvJ+qiNgmXXssgCl5vHs22y/RIgeOnDhkj81aB9Evx9iR7DOtyRBxnovrbN5gDwX
                    m6IDw3fRhZQrVwZ816/eN+1sqpIMZF4oo4kRA4b64U04ex67A/6BwDDQ3LH0mD4d
                    EwIDAQAB
                    -----END PUBLIC KEY-----
    
```

To use *asymmetric keys*, `type` MUST be set to `RS256` or `RS512` and the `algo` JWT header MUST match. The secret in this case is the public key of the issuer.

Clients should pass the token using an `Authentication: Bearer` header, eg:

```
Authentication: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ
```

While this is compatible with OAuth 2.0, use of such a protocol is outside of the scope of SwaggerBundle and entirely optional. For more information on using JWT Bearer tokens in OAuth, refer to [this spec](http://tools.ietf.org/html/draft-ietf-oauth-jwt-bearer-07).
 
### Integration Into Symfony Security

When enabled, `JwtAuthenticator` will be used for any operations referencing a `SecurityDefinition` of type `apiKey` or `oath2`. You will need a *user provider*, which will be passed the
 'prn' value when invoking `loadUserByUsername`. Trivial example using 'in memory':
 
```yml
security:
    firewalls:
        secured_area:
            pattern: ^/
            stateless: true
            simple_preauth:
                authenticator: swagger.auth.authenticator.jwt
            provider: in_memory

    providers:
        in_memory:
            memory:
                users:
                    joe:
                        roles: 'IS_AUTHENTICATED_FULLY'
```

With JWT enabled, SwaggerBundle will configure `access_control` to match Swagger paths, and require the `IS_AUTHENTICATED_FULLY` role.

When using `SecurityDefinition` type `oauth2`, it would be possible to translate *scopes* to Symfony roles and add them to the user. This is not currently implemented (yet). 

# Developing

__NOTE:__ In order to use development tools, the `require-dev` dependencies are needed, as well as setting the `dev` configuration option:

```yml
swagger:
    dev: true # Or perhaps "%kernel.debug%"
```

## Amending Your Swagger Document
 
SwaggerBundle adds some standardized behavior, this should be reflected in your Swagger document. Instead of doing this manually, you can use the `swagger:document:amend` command.

## Generating Resource Classes
 
SwaggerBundle can generate classes for you based on your Swagger resource definitions. 
You can use the resulting classes as DTO-like objects for your services, or create Doctrine mapping config for them. Obviously this requires you to enable object serialization.
The resulting classes will have JMS\Serializer annotations by default, the use of which is optional, remove them if you're using the standard Symfony serializer.

See `app/console swagger:generate:resources --help` for more details.

## Functional Testing Your API

TODO
   
## License

KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).