<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Dev\Test\ApiTestCase;
use KleijnWeb\SwaggerBundle\Tests\Security\Authenticator\JwtAuthenticatorTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SecuredPetStoreApiTest extends WebTestCase
{
    use ApiTestCase;

    // @codingStandardsIgnoreStart
    const KEY_ONE_TOKEN = JwtAuthenticatorTest::TEST_TOKEN;
    const KEY_TWO_TOKEN = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImtleVR3byJ9.eyJwcm4iOiJqb2huIiwiaXNzIjoiaHR0cDovL2FwaS5zZXJ2ZXIyLmNvbS9vYXV0aDIvdG9rZW4ifQ.vdGhD5E4Ibj2Tndlh_0pPgJsOuRUpAn1QYu5miB6qwjrXhKCicuTKOuC9x2_2ErUOApv5KiblYds_gcWONdGKx1tQyQa1dsuhrkiVn_VJAsaaix8nJiHAuNv-ukm8mnSWJoVuOcTQIQG8IaupviyphEAEdjrm9QQhvzERgdFUT4bdCdfywrC37oYEAH5bHpiiUK2UzyNuUIHwOP_gWODodbEWRJOxtefwJ_vdpqHvSZzyW7Vei4mCtr2vE1k2qBvG_Qjw2ebLfEdX58k6-eYa7phle9hYjA_q-I8Y-S1ulBiVf_tpvayk8-4lWup9Wbg_BT2vDJOidQgM4l9jV9QHg';
    // @codingStandardsIgnoreEnd

    /**
     * Use config_secured.yml
     *
     * @var bool
     */
    protected $env = 'secured';

    /**
     * TODO Temporary workaround
     * @see https://github.com/kleijnweb/swagger-bundle/issues/16
     *
     * @var bool
     */
    protected $validateErrorResponse = false;


    public static function setUpBeforeClass()
    {
        static::initSchemaManager(__DIR__ . '/PetStore/app/petstore.yml');
    }

    /**
     * @test
     */
    public function canFindPetsByStatus()
    {
        $this->defaultServerVars = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::KEY_ONE_TOKEN
        ];
        $params = ['status' => 'available'];

        $this->get('/v2/pet/findByStatus', $params);
    }

    /**
     * @test
     */
    public function canAddPet()
    {
        $this->defaultServerVars = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::KEY_ONE_TOKEN
        ];

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
    public function canFindPetsByStatusUsingAsymmetricKeySecret()
    {
        $this->defaultServerVars = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::KEY_TWO_TOKEN
        ];
        $params = ['status' => 'available'];

        $this->get('/v2/pet/findByStatus', $params);
    }

    /**
     * @test
     */
    public function canAddPetUsingAsymmetricKeySecret()
    {
        $this->defaultServerVars = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . self::KEY_TWO_TOKEN
        ];

        $content = [
            'name'      => 'Joe',
            'photoUrls' => ['foobar']
        ];

        $responseData = $this->post('/v2/pet', $content);

        $this->assertSame('Joe', $responseData->name);
        $this->assertSame('available', $responseData->status);
    }
}
