# Error Handling

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