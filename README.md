# KleijnWeb\SwaggerBundle 
[![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/swagger-bundle/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/swagger-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/swagger-bundle/v/stable)](https://packagist.org/packages/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger ([Open API](https://openapis.org/)) specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig.

**HEADS UP:** _You are looking at the main (4.0 ALPHA) development line, which is PHP 7 only. SwaggerBundle 3.x is stable and works with PHP 5.4+._

## Contract First

We say your OpenAPI definition *is* your config, and strive towards 'minimal additional config'. At the core, SwaggerBundle does three things:

 1. Configure Symfony Routing
 2. Validate input
 3. Coerce/transform in- and output

## Usage

1. Create an OpenAPI document, for example using http://editor.swagger.io/.
2. Install and configure this bundle 
3. Create one or more controllers (as services)

Check out the [User Documentation](docs.md) for more details. 

## What's new in 4.0?

SwaggerBundle 4.0 is currently in the alpha stage. Much of the behavior dealing with OpenAPI documents has been moved to [KleijnWeb\PhpApi\Descriptions](https://github.com/kleijnweb/php-api-descriptions).

### Serialization
 
Support for 3rd party serializers has been replaced by a new _API Description Based_ hyrator ([KleijnWeb\PhpApi\Hydrator](https://github.com/kleijnweb/php-api-hydrator)). The hydrator is optional, but without it in- and output will be `stdClass|stdClass[]`, not a combination of arrays and associative arrays as was the `<4.0` default. 

### Testing
 
The dependency on `SwaggerAssertions` has been removed, as response validation is now facilitated by `KleijnWeb\PhpApi\Descriptions` and integrated into the request cycle. Recommended to keep enabled until SYMFONY_ENV == prod.

### Errors
 
`vnd.error` support has been removed in favor of simpler error responses. This also gets rid of some dependencies that were unneeded for most use cases.

## What's the roadmap?

 - 4.0 ALPHA: 3.x compatibility plugins
 - 4.0 BETA: feature freeze, refactoring, lots of testing (*ETA 2016-09-01*).
 - 4.0 STABLE: ETA 2016-10-01
 - 4.1: Stubbing responses
 - 4.2: Proxying, Registry App
 - 4.3: Service routing, [EIP](https://en.wikipedia.org/wiki/Enterprise_Integration_Patterns) recipes.
 
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
 
## Contributing

Pull request are *very* welcome, as long as:

 - All automated checks were successful
 - Merge would not violate semantic versioning 
 - When applicable, the relevant documentation is updated
 
## License
 
KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).