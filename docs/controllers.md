## Controllers

### Controller Resolution

All controllers must be defined as services in the DI container.

SwaggerBundle sees an `operation id` as composed from the following parts:

```
[router].[controller]:[method]
```

The `router` segment defaults to `swagger.controller`, but _can_ be overwritten at the `Path Object` level using `x-router`:

```yaml
paths:
  x-router: my.default.controller.namespace
  /foo:
    ...
  /foo/{bar}:
    ..
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
    ..
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
    ..
```

### Controller Conventions

Controller methods that expect content can either get the content from the `Request` object, or add a parameter named identical to the parameter with `in: body` set.
Any of these will work (assuming the `in: body` parameter is named `body` in your spec):

```php
public function placeOrder(Request $request)
{
    /** @var array $order */
    $order = $request->attributes->get('body');

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

__NOTE:__ SwaggerBundle applies some type conversion to query and path parameters and adds the converted values to the Request `attributes`. 
Using `Request::get()` will give precedence to parameters in `query`. These values will be 'raw', using `attributes` is preferred.

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

All (type-casted) parameters can be added to the signature, since they are attributes when the controller is invoked. This is standard Symfony behaviour.