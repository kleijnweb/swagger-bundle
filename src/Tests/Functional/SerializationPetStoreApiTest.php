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
     * TODO Temporary workaround
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
     * Enable serialization
     */
    protected function setUp()
    {
        parent::setUp();

        $container = $this->client->getContainer();
        /** @var SerializerAdapter $serializer */
        $serializerAdapter = $container->get('swagger.serializer');
        /** @var Serializer $serializer */
        $jmsSerializer = $container->get('swagger.serializer.jms');
        $serializerAdapter->setTarget($jmsSerializer);
        /** @var SerializationTypeResolver $typeResolver */
        $typeResolver = $container->get('swagger.serializer.type_resolver');
        /** @var ContentDecoder $contentDecoder */
        $contentDecoder = $container->get('swagger.request.transformer.content_decoder');
        $contentDecoder->setTypeResolver($typeResolver);
    }

    /**
     * @test
     */
    public function canPlaceOrder()
    {
        $params = [
            'petId'    => 987654321,
            'quantity' => 10,
        ];

        $actual = $this->post('/v2/store/order', $params);
        $this->assertSame('placed', $actual->status);
        $this->assertSame($params['petId'], $actual->petId);
        $this->assertSame($params['quantity'], $actual->quantity);
        $this->assertInternalType('integer', $actual->id);
    }
}
