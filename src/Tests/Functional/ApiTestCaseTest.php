<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Test\ApiResponseErrorException;
use KleijnWeb\SwaggerBundle\Test\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiTestCaseTest extends WebTestCase
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
     * @expectedException \KleijnWeb\SwaggerBundle\Test\ApiResponseErrorException
     */
    public function notFoundApiCallThrowsException()
    {
        $this->get('/foo');
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Test\ApiResponseErrorException
     * @expectedExceptionCode 405
     */
    public function methodNotSupportedReturnsMethodNotAllowed()
    {
        $this->patch('/v2/pet/findByStatus', []);

    }
}
