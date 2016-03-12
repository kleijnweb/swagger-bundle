# Routing

## Route Matching

To view the routes added by SwaggerBundle, you can use Symfony's `debug:router`. Route keys include the Swagger spec base filename to prevent collisions. For path parameters,
SwaggerBundle adds additional requirements to the routes. This way `/foo/{bar}` and `/foo/bar` wont conflict when `bar` is defined to be an integer. This also supports Swaggers `pattern`.
and `enum` when dealing with string path parameters.