# KleijnWeb\SwaggerBundle 
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dcd0367a-0371-443a-b258-5c9356cbc953/small.png)](https://insight.sensiolabs.com/projects/dcd0367a-0371-443a-b258-5c9356cbc953)

[![Build Status](https://travis-ci.org/kleijnweb/swagger-bundle.svg?branch=master)](https://travis-ci.org/kleijnweb/swagger-bundle)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/swagger-bundle/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/swagger-bundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/swagger-bundle/?branch=master)
[![Latest Unstable Version](https://poser.pugx.org/kleijnweb/swagger-bundle/v/unstable)](https://packagist.org/packages/kleijnweb/swagger-bundle)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/swagger-bundle/v/stable)](https://packagist.org/packages/kleijnweb/swagger-bundle)

Invert your workflow (contract first) using Swagger ([Open API](https://openapis.org/)) specs and set up a Symfony REST app with minimal config.

Aimed to be lightweight, this bundle does not depend on FOSRestBundle or Twig.

**HEADS UP:** _You are looking at the main (4.0 BETA) development line, which is PHP 7 only. SwaggerBundle 3.x is stable, maintained, and works with PHP 5.4+._

For a working example, check out https://github.com/kleijnweb/swagger-bundle-example.

## Contract First

We say your OpenAPI definition *is* your config, and strive towards 'minimal additional config'. At the core, SwaggerBundle does three things:

 1. Configure Symfony routing
 2. Validate input
 3. Coerce/transform in- and output

## Usage

1. Create an OpenAPI document, for example using http://editor.swagger.io/.
2. Install and configure this bundle 
3. Create one or more controllers (as services)

Check out the [User Documentation](docs.md) for more details. 

## What's new in 4.0?

SwaggerBundle 4.0 is currently in the beta stage. Much of the behavior dealing with OpenAPI documents has been moved to [KleijnWeb\PhpApi\Descriptions](https://github.com/kleijnweb/php-api-descriptions).

### Routing
 
Now using [kleijnweb/php-api-routing-bundle](https://github.com/kleijnweb/php-api-routing-bundle) with a number of small improvements.

### Security Integration
 
Request matching, voting, OpenAPI configured RBAC. See [docs](docs.md#security).

### Serialization/Hydration
 
Support for 3rd party serializers has been replaced by a new _API Description Based_ (de-)hyrator. Hydrating of untyped data is expected to be `stdClass|stdClass[]`, not a combination of arrays and associative arrays as was the `<4.0` default.

The new procoess has support for JSON-Schema specifics such as default values and smart NULL/undefined handling, as well as high extensibility. This allows you to hook pretty much anything you like into the (de-)hydration process, such as loading objects to be populated with request values from a data store or preserving identity of objects that occur more than once in a request. 

### Testing
 
The dependency on `SwaggerAssertions` has been removed, as response validation is now facilitated by `KleijnWeb\PhpApi\Descriptions` and [integrated into the request cycle](docs.md#testing).

### Errors And Exceptions
 
 - `vnd.error` support has been removed in favor of simpler error responses. This also gets rid of some dependencies that were unneeded for most use cases.
 - `HttpError` now supports `AccessDeniedException`.

## FAQ
 
  - Will SwaggerBundle do `x`?
  
 If `x` is any of these, the answer will probably stay 'no':
 
  * Handle Form posts.
  * Generate API documentation.
  * Support Symfony sub-requests. You won't miss them.
  * Support XML.
  
## Symfony Compatibility
 
SwaggerBundle is tested against Symfony 2.8.18 and the latest release (4.x.x), at least once a week.

## Notes
 
Go to the [release page](https://github.com/kleijnweb/swagger-bundle/releases) to find details about the latest release.
 
## Contributing

Pull request are *very* welcome, as long as:

 - All automated checks were successful
 - Merge would not violate semantic versioning 
 - When applicable, the relevant documentation is updated
 
## License
 
KleijnWeb\SwaggerBundle is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
