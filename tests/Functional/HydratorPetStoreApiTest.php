<?php declare(strict_types = 1);
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
class HydratorPetStoreApiTest extends WebTestCase
{
    use ApiTestCase;

    /**
     * @var string
     */
    protected $env = 'hydrator';

    /**
     * @group functional
     * @test
     */
    public function canPlaceOrder()
    {
        $content = [
            'petId'    => 987654321,
            'quantity' => 10,
            'shipDate' => '2016-01-01T01:00:00Z',
            'complete' => false
        ];

        $actual = $this->post('/v2/store/order', $content);
        $this->assertSame('placed', $actual->status);
        $this->assertSame($content['petId'], $actual->petId);
        $this->assertSame($content['quantity'], $actual->quantity);

        $this->assertTrue($actual->complete);
        $this->assertSame('2016-01-02T01:00:00+0000', $actual->shipDate);

        $this->assertInternalType('integer', $actual->id);
    }

    /**
     * @group functional
     * @test
     */
    public function canPostPet()
    {
        $content = [
            'name'      => 'fido',
            'photoUrls' => ['1', '2'],
            'quantity'  => 10,
            'category'  => ['name' => 'dogs']
        ];

        $actual = $this->post('/v2/pet', $content);
        $this->assertSame($content['name'], $actual->name);
        $this->assertSame($content['photoUrls'], $actual->photoUrls);
        $this->assertInternalType('integer', $actual->id);
        $this->assertObjectNotHasAttribute('quantity', $actual);
        $this->assertObjectHasAttribute('category', $actual);
        $this->assertObjectHasAttribute('id', $actual->category);
    }
}
