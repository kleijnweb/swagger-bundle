## Controllers

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