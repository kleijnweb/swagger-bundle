<?php declare(strict_types = 1);
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
 * @group  functional
 */
class BasicSecuredApiTest extends WebTestCase
{
    use ApiTestCase;

    /**
     * @var string
     */
    protected $env = 'secure_basic';

    /**
     * @test
     */
    public function canGetUnsecuredContentWithoutAuth()
    {
        $string = $this->get('/basic-auth/v1/unsecured');

        $this->assertSame($string, 'UNSECURED CONTENT');
    }

    /**
     * @test
     */
    public function cannotGetSecuredContentWithoutAuth()
    {
        $this->setExpectedException(ApiResponseErrorException::class, '', 401);

        $this->get('/basic-auth/v1/secure');
    }

    /**
     * @test
     */
    public function canGetSecuredContentWithAuth()
    {
        $string = $this->get('/basic-auth/v1/secure', [], ['PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'password']);

        $this->assertSame($string, 'SECURED CONTENT');
    }
}
