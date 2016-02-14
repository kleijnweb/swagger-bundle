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
class BasicPetStoreApiTest extends WebTestCase
{
    use ApiTestCase;

    /**
     * Use config_basic.yml
     *
     * @var bool
     */
    protected $env = 'basic';

    public static function setUpBeforeClass()
    {
        static::initSchemaManager(__DIR__ . '/PetStore/app/swagger/petstore.yml');
    }

    /**
     * @test
     */
    public function canFindPetsByStatus()
    {
        $params = ['status' => 'available'];

        $this->get('/v2/pet/findByStatus', $params);
    }

    /**
     * @test
     */
    public function canAddPet()
    {
        $content = [
            'name'      => 'Joe',
            'photoUrls' => ['foobar']
        ];

        $responseData = $this->post('/v2/pet', $content);

        $this->assertSame('Joe', $responseData->name);
        $this->assertSame('available', $responseData->status);
    }

    /**
     * @test
     */
    public function canGetPetById()
    {
        $id = rand();

        $responseData = $this->get('/v2/pet/' . $id);

        $this->assertSame($id, $responseData->id);
    }
}
