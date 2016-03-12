# Routing

## Route Matching

To view the routes added by SwaggerBundle, you can use Symfony's `debug:router`. Route keys include the Swagger spec base filename to prevent collisions. For path parameters,
SwaggerBundle adds additional requirements to the routes. This way `/foo/{bar}` and `/foo/bar` wont conflict when `bar` is defined to be an integer. 
This also supports Swaggers `pattern` and `enum` when dealing with string path parameters.

## Controller Resolution

All controllers must be defined as services in the DI container. SwaggerBundle sees an `operation id` as composed from the following parts:

```
[router].[controller]:[method]
```

`Router` is a DI key namespace in this context. The `router` segment defaults to `swagger.controller`, but can be overwritten at the `Path Object` level using `x-router`:

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    ...
```

The `controller` segments defaults to the resource name as extracted from the path by convention. For example, for path `/foo/something` the default router + controller would be: `swagger.controller.foo`.

You can override the whole of `[router].[controller]` using `x-router-controller`. This will not only override the default, but any declaration of `x-router`, too:

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    ...
```

The following is also supported (set controller for a specific method):

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    patch:
      x-router-controller: an.alternate.di.namespace.controller
    ...
```

Finally, the `method` segment defaults to the HTTP method name, but may be overridden using Swagger's `operationId` or `x-router-controller-method`. Note the Swagger spec requires `operationId` to be unique, so while `operationId` can contain only the method name, you're usually better off using `x-router-controller-method`.
You can also use a fully quantified operation id using double colon notation, eg "my.controller.namespace.myresource:methodName". Combining `x-router` or `x-router-controller` and a quantified `operationId` ignores the former.

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    post:
      # Ingores declarations above
      operationId: my.controller.namespace.myresource:methodName
    ...
```

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    post:
      # Resolves to 'my.default.controller.namespace.foo:methodName'
      x-router-controller-method: methodName
    ...
```

```yaml
paths:
  /foo:
    ...
  /foo/{bar}:
    x-router-controller: an.alternate.di.namespace.controller
    post:
      # Same as above. Valid, but discouraged
      operationId: methodName
    ...
```