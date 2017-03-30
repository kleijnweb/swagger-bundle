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
 * @group functional
 */
class ApiTestCaseTest extends WebTestCase
{
    use ApiTestCase;

    /**
     * @var string
     */
    protected $env = 'basic';

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
