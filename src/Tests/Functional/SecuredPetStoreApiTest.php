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
class SecuredPetStoreApiTest extends ApiTestCase
{
    // @codingStandardsIgnoreStart
    const KEY_ONE_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImtleU9uZSJ9.eyJwcm4iOiJqb2huIn0.jLAsPUHRZuV7X403lhaHoj6Ld77cxg9Q9Lg3sDa-rTA';
    const KEY_TWO_TOKEN = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImtleVR3byJ9.eyJwcm4iOiJqb2huIn0.IqAnXTaVbeIkQgZ5o0waUgyiE44IySnvR6Qrm-Sq5NP-vt2ATpDKSlHmBoUqm1yD6CZNXP-vVnr_bau3ecw4YHAPjWSe8gA2OU2K59lcRn6vuKpO4V2pLZxCR5KGRxfb04yLhxnDHK6OUwsai8Ll29_Xudkly9OSr7QROObkIzUJdz0nBoDDDDTKlLkiKX1bP7irmEq37ys-mP4CEG3fIS4s1QyxFcJm5LvFCdcuMztwpmhGfJWRNuG2rOPY9_z0vwp_eg4tD-hZRLCxmtIli_RYCYIhbv9bojR6Nuh3t3dk3ttww0DuuHHAepV97Plwb46jch0gwg_XWwKaDTudCw';
    // @codingStandardsIgnoreEnd

    /**
     * Use config_secured.yml
     *
     * @var bool
     */
    protected $env = 'secured';

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

        $params = [
            'name'      => 'Joe',
            'photoUrls' => ['foobar']
        ];

        $responseData = $this->post('/v2/pet', $params);

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

        $params = [
            'name'      => 'Joe',
            'photoUrls' => ['foobar']
        ];

        $responseData = $this->post('/v2/pet', $params);

        $this->assertSame('Joe', $responseData->name);
        $this->assertSame('available', $responseData->status);
    }
}
