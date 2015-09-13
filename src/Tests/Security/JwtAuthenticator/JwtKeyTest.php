<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Tests\Security\Authenticator\JwtAuthenticator;

use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\JwtKey;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JwtKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function constructionWillFailWhenSecretNotInOptions()
    {
        new JwtKey([]);
    }

    /**
     * @test
     */
    public function serializingWillClearSecret()
    {
        $key = new JwtKey(['secret' => 'Buy the book']);
        $actual = unserialize(serialize($key));
        $refl = new \ReflectionClass($actual);
        $property = $refl->getProperty('secret');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($actual));
    }

    /**
     * @test
     */
    public function validateTokenWillCallVerifySignatureOnToken()
    {
        $key = new JwtKey(['secret' => 'Buy the book']);
        $token = $this->getMockBuilder(
            'KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\JwtToken'
        )->disableOriginalConstructor()->getMock();

        $token->expects($this->once())
            ->method('validateSignature')
            ->with('Buy the book', $key->getSignatureValidator());

        $token->expects($this->once())
            ->method('getClaims')
            ->willReturn([]);

        $token->expects($this->once())
            ->method('getHeader')
            ->willReturn([]);


        $key->validateToken($token);
    }

    /**
     * @test
     */
    public function willGetRsaSignatureValidatorWhenTypeIsNotSpecified()
    {
        $key = new JwtKey(['secret' => 'Buy the book']);
        $actual = $key->getSignatureValidator();
        $this->assertInstanceOf(
            'KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\HmacValidator',
            $actual
        );
    }

    /**
     * @test
     */
    public function willGetRsaSignatureValidatorWhenTypeIsRsa()
    {
        $key = new JwtKey(['secret' => 'Buy the book', 'type' => JwtKey::TYPE_RSA]);
        $actual = $key->getSignatureValidator();
        $this->assertInstanceOf(
            'KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\RsaValidator',
            $actual
        );
    }
}
