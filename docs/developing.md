# Developing Your API

# Utilities

See [swagger-bundle-tools](https://github.com/kleijnweb/swagger-bundle-tools).

## Functional Testing Your API

The easiest way to create functional tests for your API, is by using mixin `ApiTestCase`. This will provide you with some convenience methods (`get()`, `post()`, `put()`, etc) and 
will validate responses using SwaggerAssertions to ensure the responses received are compliant with your Swagger spec. Example:

```php
class PetStoreApiTest extends WebTestCase
{
    use ApiTestCase;
    
    /**
     * Use config_basic.yml
     *
     * @var bool
     */
    protected $env = 'basic';

    /**
     * @see https://github.com/kleijnweb/swagger-bundle/issues/16
     *
     * @var bool
     */
    protected $validateErrorResponse = false;

    /**
     * Init response validation, point to your spec
     */
    public static function setUpBeforeClass()
    {
        static::initSchemaManager(__DIR__ . '/path/to/a/spec.yml');
    }

    /**
     * @test
     */
    public function placingAnOrderWillReturnDataWithOrderIsPlaced()
    {
        $content = [
            'petId'    => 987654321,
            'quantity' => 10,
        ];

        $actual = $this->post('/v2/store/order', $content);
        $this->assertSame('placed', $actual->status);
        $this->assertSame($content['petId'], $actual->petId);
        $this->assertSame($content['quantity'], $actual->quantity);
    }
}
```

When using ApiTestCase, initSchemaManager() will also validate your Swagger spec against the official schema to ensure it is valid.