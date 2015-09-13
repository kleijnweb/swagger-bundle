<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Tests\Security\Authenticator;

use KleijnWeb\SwaggerBundle\Dev\Test\ApiRequest;
use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator;
use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\JwtKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\User\User;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JwtAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    // @codingStandardsIgnoreStart

    /**
     * Created using jwt.io
     */
    const TEST_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImtleU9uZSJ9.eyJwcm4iOiJqb2huIn0.jLAsPUHRZuV7X403lhaHoj6Ld77cxg9Q9Lg3sDa-rTA';


    /**
     * @var array
     */
    private static $keyConfig = [
        'keyOne' =>
            [
                'issuer' => 'http://api.server1.com/oauth2/token',
                'secret' => 'A Pre-Shared Key',
                'type'   => 'HS256',
            ],
        'keyTwo' =>
            [
                'issuer' => 'http://api.server2.com/oauth2/token',
                'type'   => 'RS256',
                'secret' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqGKukO1De7zhZj6+H0qtjTkVxwTCpvKe4eCZ0F',
            ],
    ];

    // @codingStandardsIgnoreEnd

    /**
     * @test
     */
    public function getGetKeysUsingIndexesInConfig()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);

        $this->assertInstanceOf(JwtKey::class, $authenticator->getKeyById('keyOne'));
        $this->assertInstanceOf(JwtKey::class, $authenticator->getKeyById('keyTwo'));
    }

    /**
     * @test
     */
    public function willGetSingleKeyWhenKeyIdIsNull()
    {
        $config = self::$keyConfig;
        unset($config['keyTwo']);

        $authenticator = new JwtAuthenticator($config);

        $this->assertInstanceOf(JwtKey::class, $authenticator->getKeyById(null));
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function willFailWhenTryingToGetKeyWithoutIdWhenThereAreMoreThanOne()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);

        $this->assertInstanceOf(JwtKey::class, $authenticator->getKeyById(null));
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function willFailWhenTryingToGetUnknownKey()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);

        $this->assertInstanceOf(JwtKey::class, $authenticator->getKeyById('blah'));
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function willFailWhenTryingToGetUserNameFromClaimsWithoutPrn()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);

        $authenticator->getUsername([]);
    }

    /**
     * @test
     */
    public function canGetUserNameFromClaims()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);

        $authenticator->getUsername(['prn' => 'johndoe']);
    }

    /**
     * @test
     */
    public function authenticateTokenWillSetUserFetchedFromUserProviderOnToken()
    {
        $claims = ['prn' => 'john'];
        $authenticator = new JwtAuthenticator(self::$keyConfig);
        $anonToken = new PreAuthenticatedToken('foo', $claims, 'myprovider');
        $userProvider = $this->getMockBuilder(
            'Symfony\Component\Security\Core\User\UserProviderInterface'
        )->getMockForAbstractClass();

        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('john')
            ->willReturn(new User('john', 'hi there'));
        $authenticator->authenticateToken($anonToken, $userProvider, 'myprovider');
    }

    /**
     * @test
     */
    public function supportsPreAuthToken()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);

        $securityToken = new PreAuthenticatedToken('foo', 'bar', 'myprovider');
        $actual = $authenticator->supportsToken($securityToken, 'myprovider');
        $this->assertTrue($actual);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function willFailWhenApiKeyNotFoundInHeader()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);
        $request = new Request();
        $authenticator->createToken($request, 'myprovider');
    }

    /**
     * @test
     */
    public function canGetAnonTokenWithClaims()
    {
        $authenticator = new JwtAuthenticator(self::$keyConfig);
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . self::TEST_TOKEN);
        $token = $authenticator->createToken($request, 'myprovider');

        $expected = ["prn" => "john"];
        $this->assertSame($expected, $token->getCredentials());
    }
}
