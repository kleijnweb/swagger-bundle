<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Test\ApiResponseErrorException;
use KleijnWeb\SwaggerBundle\Test\ApiTestCase;
use KleijnWeb\SwaggerBundle\Test\ApiTestClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 * @group  functional
 */
class RbacApiTest extends WebTestCase
{
    use ApiTestCase;

    /**
     * @var string
     */
    protected $env = 'secure_rbac';

    protected function setUp()
    {
        //NOOP
    }

    /**
     * @test
     */
    public function canGetUserContentAsUser()
    {
        $this->createClientForUser('user');

        $string = $this->get('/basic-auth/v1/rbac-user');

        $this->assertSame($string, 'USER CONTENT');
    }

    /**
     * @test
     */
    public function cannotGetUserContentAsGuest()
    {
        $this->createClientForUser('guest');

        $this->expectException(ApiResponseErrorException::class);
        $this->expectExceptionCode(Response::HTTP_FORBIDDEN);

        $string = $this->get('/basic-auth/v1/rbac-user');

        $this->assertSame($string, 'USER CONTENT');
    }

    /**
     * @test
     */
    public function cannotGetUserContentWithoutAuth()
    {
        $this->createApiTestClient();

        $this->expectException(ApiResponseErrorException::class);
        $this->expectExceptionCode(Response::HTTP_UNAUTHORIZED);

        $this->get('/basic-auth/v1/rbac-user');
    }

    /**
     * @test
     */
    public function canGetAdminContentAsAdmin()
    {
        $this->createClientForUser('admin');

        $string = $this->get('/basic-auth/v1/rbac-admin');

        $this->assertSame($string, 'ADMIN CONTENT');
    }

    /**
     * @test
     */
    public function cannotGetAdminContentAsUser()
    {
        $this->createClientForUser('user');

        $this->expectException(ApiResponseErrorException::class);
        $this->expectExceptionCode(Response::HTTP_FORBIDDEN);

        $string = $this->get('/basic-auth/v1/rbac-admin');

        $this->assertSame($string, 'USER CONTENT');
    }

    private function createClientForUser(string $user)
    {
        $this->client = new ApiTestClient(
            static::createClient(
                ['environment' => $this->getEnv(), 'debug' => true],
                ['PHP_AUTH_USER' => $user, 'PHP_AUTH_PW' => 'password']
            )
        );
    }
}
