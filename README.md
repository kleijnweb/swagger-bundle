# KleijnWeb\SwaggerBundle

Invert your workflow (contract first) using Swagger specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig.

__IMPORTANT NOTE__: This development version of SwaggerBundle only support json in- and ouput, and only YAML Swagger defintions.

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
    resource: "%kernel.root_dir%/../web/yourapp.yml"
    type: swagger
```
## Functional Testing Your API

TODO

# Functional Details

## Controllers

When a call is made that is satisfiable by SwaggerBundle, it uses Symfony Dependency Injection service keys to find the
delegation target of the request. It will assume the first segment in the Swagger paths is a resource name,
and looks for a service with the key `swagger.controller.%resource_name%`. The class method to be called by defaults corresponds
to the HTTP method name, but may be overridden by including `operationId` in your spec.

## Exception Handling

Any exceptions are caught, logged by the @logger service, and result in `application/vnd.error+json` or `application/vnd.error+xml`,
depending on Content Negotiation rules (__NOT IMPLEMENTED YET__).

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
   
## License

KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).