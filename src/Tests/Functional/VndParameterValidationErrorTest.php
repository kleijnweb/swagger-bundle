<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Response\VndValidationErrorFactory;
use KleijnWeb\SwaggerBundle\Test\ApiResponseErrorException;
use KleijnWeb\SwaggerBundle\Test\ApiTestCase;
use Nocarrier\Hal;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndParameterValidationErrorTest extends WebTestCase
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
    public function parameterValidationErrorWillContainDefaultMessageAndLogref()
    {
        try {
            $this->get('/v2/pet/findByStatus', ['status' => 'bogus']);
        } catch (ApiResponseErrorException $e) {
            $data = Hal::fromJson($e->getJson(), 10)->getData();
            $this->assertSame(VndValidationErrorFactory::DEFAULT_MESSAGE, $data['message']);
            $this->assertRegExp('/[0-9a-z]+/', $data['logref']);

            return;
        }
        $this->fail("Expected exception");
    }

    /**
     * @test
     */
    public function parameterValidationErrorWillContainSpecificationPointer()
    {
        try {
            $this->get('/v2/pet/findByStatus', ['status' => 'bogus']);
        } catch (ApiResponseErrorException $e) {
            $error = Hal::fromJson($e->getJson(), 10);
            $resource = $error->getFirstResource('errors');
            $specLink = 'http://petstore.swagger.io/swagger/petstore.yml#/paths/~1pet~1findByStatus/get/parameters/0';
            $this->assertSame($specLink, $resource->getUri());

            return;
        }
        $this->fail("Expected exception");
    }

    /**
     * @test
     */
    public function parameterValidationErrorWillContainSpecificationPointerForBodyParameter()
    {
        $this->markTestIncomplete('Not working with JSON-Schema version');

        $url = 'http://petstore.swagger.io/swagger/petstore.yml';
        try {
            $this->post('/v2/store/order', []);
        } catch (ApiResponseErrorException $e) {
            $error = Hal::fromJson($e->getJson(), 10);
            $resource = $error->getFirstResource('errors');
            $specLink = $url . '#/paths/~1pet~1findByStatus/get/parameters/0/body/properties/quantity';
            $this->assertSame($specLink, $resource->getUri());

            return;
        }
        $this->fail("Expected exception");
    }

    /**
     * @test
     */
    public function parameterValidationErrorWillContainSchemaPointer()
    {
        try {
            $this->get('/v2/pet/findByStatus', ['status' => 'bogus']);
        } catch (ApiResponseErrorException $e) {
            $error = Hal::fromJson($e->getJson(), 10);
            $resource = $error->getFirstResource('errors');
            $data = $resource->getData();
            $this->assertSame('/paths/~1pet~1findByStatus/get/x-request-schema/properties/status', $data['path']);

            return;
        }
        $this->fail("Expected exception");
    }

    /**
     * @test
     */
    public function parameterValidationErrorCanContainMultipleErrors()
    {
        try {
            $this->get('/v2/user/login');
        } catch (ApiResponseErrorException $e) {
            $error = Hal::fromJson($e->getJson(), 10);
            $resources = $error->getResources();
            $this->assertArrayHasKey('errors', $resources);
            /**
             * @var int $i
             * @var Hal $resource
             */
            foreach ($resources['errors'] as $i => $resource) {
                $data = $resource->getData();
                if ($i == 0) {
                    $this->assertSame('/paths/~1user~1login/get/x-request-schema/properties/username', $data['path']);
                    $uri = 'http://petstore.swagger.io/swagger/petstore.yml#/paths/~1user~1login/get/parameters/0';
                    $this->assertSame($uri, $resource->getUri());
                    continue;
                }
                $this->assertSame('/paths/~1user~1login/get/x-request-schema/properties/password', $data['path']);
                $uri = 'http://petstore.swagger.io/swagger/petstore.yml#/paths/~1user~1login/get/parameters/1';
                $this->assertSame($uri, $resource->getUri());
            }

            return;
        }
        $this->fail("Expected exception");

    }
}
