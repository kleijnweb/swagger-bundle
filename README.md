# KleijnWeb\SwaggerBundle 
[![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/swagger-bundle/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/swagger-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/swagger-bundle/v/stable)](https://packagist.org/packages/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger ([Open API](https://openapis.org/)) specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig.

## Contract First

SwaggerBundle is built around the idea of "contract first". Other "Swagger Bundles" see an OpenAPI definition as documentation, that you generate using config and annotations.

We say your OpenAPI definition *is* your config, and strive towards 'minimal additional config'. At the core, SwaggerBundle does three things:

 1. Configure Symfony Routing
 2. Validate input
 3. Coerce/transform in- and output

## Usage

1. Create a Swagger file, for example using http://editor.swagger.io/.
2. Install and configure this bundle 
3. Create one or more controllers (as services), doing the actual work, whatever that may be.

## Documentation Topics

 - [Installation and configuration](docs/config.md)
 - [Routing](docs/routing.md)
 - [Controllers](docs/controllers.md)
 - [Errors and validation](docs/errors.md)
 - [Serialization](docs/serialization.md)
 - [Responses](docs/responses.md)
 - [Caching](docs/caching.md)
 - [API Development](docs/developing.md)
 - [Contributing](docs/contributing.md)
 
## FAQ

 - Will SwaggerBundle do `x`?
 
If `x` is any of these, the answer will probably stay 'no':

 * Handle Form posts.
 * Generate API documentation.
 * Mix well with GUI bundles. The bundle is biased towards lightweight API-only apps.
 * Support XML.
 
## Notes

For a pretty complete example, see [swagger-bundle-example](https://github.com/kleijnweb/swagger-bundle-example).
A minimal example is also [available](https://github.com/kleijnweb/symfony-swagger-microservice-edition).

This bundle is currently actively maintained. Go to the [release page](https://github.com/kleijnweb/swagger-bundle/releases) to find details about the latest release.

## License

KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
