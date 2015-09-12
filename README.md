# KleijnWeb\SwaggerBundle [![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig (except for dev purposes).

SwaggerBundle only supports json in- and ouput, and only YAML Swagger defintions.

# This Bundle..

## Will:

 * Validate content and parameters based on your Swagger spec.
 * Configure routing based on your Swagger spec. 
 * Handle standard responses such as 500, 400 and 404.
 * Return vnd.error responses.
 * Utilize vnd.error's `logref` to make errors traceable.
 * Resolve JSON-Schema `$ref`s in your Swagger spec to allow reusable partial specs.
 
## Can:

 * Amend your Swagger spec to include the error responses added by SwaggerBundle.
 * Generate DTO-like classes representing resources in your Swagger spec.
 * Update the resource schemas in your Swagger spec when these classes change.

## Won't:

 * Handle Form posts. JSON in and output ONLY.
 * Generate your API documentation. Use your Swagger document, plenty of options.
 * Mix well with GUI bundles. The bundle is biased towards lightweight API-only apps.
 * Do content negotiation. May support XML in the future (low priority).

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

The path here is relative to the `swagger.document.base_path` parameter. The above example would require something like this in your config:

```yml
parameters:
  swagger.document.base_path: "%kernel.root_dir%"
```
# Functional Details

## Controllers

When a call is made that is satisfiable by SwaggerBundle, it uses Symfony Dependency Injection service keys to find the
delegation target of the request. It will assume the first segment in the Swagger paths is a resource name,
and looks for a service with the key `swagger.controller.%resource_name%`. The class method to be called by defaults corresponds
to the HTTP method name, but may be overridden by including `operationId` in your spec.

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

# Developing

## Amending Your Swagger Document
 
SwaggerBundle adds some standardized behavior, this should be reflected in your Swagger document. Instead of doing this manually, you can use the `swagger:document:amend` command.

## Generating Resource Classes
 
SwaggerBundle can generate classes for you based on your Swagger resource definitions. 
You can use the resulting classes as DTO-like objects for your services, or create Doctrine mapping config for them.

See `app/console swagger:generate:resources --help` for more details.

## Functional Testing Your API

TODO
   
## License

KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).