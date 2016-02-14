<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Test\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SerializationPetStoreApiTest extends WebTestCase
{
    use ApiTestCase;

    /**
     * Use config_jms.yml
     *
     * @var bool
     */
    protected $env = 'jms';

    /**
     * Initialize SwaggerAssertions Schema Manager
     */
    public static function setUpBeforeClass()
    {
        static::initSchemaManager(__DIR__ . '/PetStore/app/swagger/petstore.yml');
    }

    /**
     * @test
     */
    public function canPlaceOrder()
    {
        $content = [
            'petId'    => 987654321,
            'quantity' => 10,
        ];

        $actual = $this->post('/v2/store/order', $content);
        $this->assertSame('placed', $actual->status);
        $this->assertSame($content['petId'], $actual->petId);
        $this->assertSame($content['quantity'], $actual->quantity);
        $this->assertInternalType('integer', $actual->id);
    }
}
