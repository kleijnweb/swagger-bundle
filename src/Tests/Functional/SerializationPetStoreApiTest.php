<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use JMS\Serializer\Serializer;
use KleijnWeb\SwaggerBundle\Dev\Test\ApiTestCase;
use KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder;
use KleijnWeb\SwaggerBundle\Serializer\SerializationTypeResolver;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SerializationPetStoreApiTest extends ApiTestCase
{
    /**
     * Use config_jms.yml
     *
     * @var bool
     */
    protected $env = 'jms';

    /**
     * TODO Temporary workaround
     * @see https://github.com/kleijnweb/swagger-bundle/issues/16
     *
     * @var bool
     */
    protected $validateErrorResponse = false;

    /**
     * Initialize SwaggerAssertions Schema Manager
     */
    public static function setUpBeforeClass()
    {
        parent::initSchemaManager(__DIR__ . '/PetStore/app/petstore.yml');
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
