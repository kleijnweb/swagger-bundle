<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Dev\Test\ApiTestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class BasicPetStoreApiTest extends ApiTestCase
{
    /**
     * Use config_basic.yml
     *
     * @var bool
     */
    protected $env = 'basic';

    /**
     * TODO Temporary workaround
     *
     * @var bool
     */
    protected $validateErrorResponse = false;


    public static function setUpBeforeClass()
    {
        parent::initSchemaManager(__DIR__ . '/PetStore/app/petstore.yml');
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
        $params = [
            'name' => 'Joe',
            'photoUrls' => ['foobar']
        ];

        $responseData = $this->post('/v2/pet', $params);

        $this->assertSame('Joe', $responseData->name);
        $this->assertSame('available', $responseData->status);
    }
}
