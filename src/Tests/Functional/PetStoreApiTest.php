<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Test\ApiTestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class PetStoreApiTest extends ApiTestCase
{
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
    public function canPlaceOrder()
    {
        $params = ['status' => 'available'];

        $this->post('/v2/store/order', $params);
    }
}
