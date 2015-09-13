<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Tests\Security\Authenticator\JwtAuthenticator;

use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\JwtKey;
use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\JwtToken;

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
            ->willReturn(['prn' => 'john']);

        $token->expects($this->once())
            ->method('getHeader')
            ->willReturn(['alg' => JwtKey::TYPE_HMAC, 'typ' => 'JWT']);

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

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenPrincipleIsMissing()
    {
        $key = new JwtKey(['secret' => 'Buy the book']);
        $key->validateClaims([]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenExpiredByExp()
    {
        $key = new JwtKey(['secret' => 'Buy the book']);
        $key->validateClaims(['prn' => 'john', 'exp' => time() - 2]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenExpiredByIatAndMinIssueTime()
    {
        $key = new JwtKey(['secret' => 'Buy the book', 'minIssueTime' => time() + 2]);
        $key->validateClaims(['prn' => 'john', 'iat' => time()]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenNotValidYet()
    {
        $key = new JwtKey(['secret' => 'Buy the book']);
        $key->validateClaims(['prn' => 'john', 'nbf' => time() + 2]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenIssuerDoesNotMatch()
    {
        $key = new JwtKey(['secret' => 'Buy the book', 'issuer' => 'me']);
        $key->validateClaims(['prn' => 'john', 'iss' => 'you']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenAudienceDoesNotMatch()
    {
        $key = new JwtKey(['secret' => 'Buy the book', 'audience' => 'me']);
        $key->validateClaims(['prn' => 'john', 'aud' => 'the neighbours']);
    }


    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenIssuerIsConfiguredAndNotInClaims()
    {
        $key = new JwtKey(['secret' => 'Buy the book', 'issuer' => 'me']);
        $key->validateClaims(['prn' => 'john']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenMinIssueTimeIsConfiguredAndIatNotInClaims()
    {
        $key = new JwtKey(['secret' => 'Buy the book', 'minIssueTime' => time()]);
        $key->validateClaims(['prn' => 'john']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenAudienceIsConfiguredAndNotInClaims()
    {
        $key = new JwtKey(['secret' => 'Buy the book', 'audience' => time()]);
        $key->validateClaims(['prn' => 'john']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validationWillFailWhenIgnoreOtherReservedAndArbitraryClaimsAreRequiredButNotInClaims()
    {
        $key = new JwtKey(
            ['secret' => 'Buy the book', 'require' => ['jti', 'typ', 'and now for something completely different']]
        );
        $key->validateClaims(['prn' => 'john']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function headerValidationWillFailWhenAlgoIsMissing()
    {
        $key = new JwtKey(
            ['secret' => 'Buy the book']
        );
        $key->validateHeader(['typ' => 'JWT']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function headerValidationWillFailWhenTypeIsMissing()
    {
        $key = new JwtKey(
            ['secret' => 'Buy the book']
        );
        $key->validateHeader(['alg' => JwtKey::TYPE_HMAC]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function headerValidationWillFailWhenAlgorithmDoesntMatchKey()
    {
        $key = new JwtKey(
            ['secret' => 'Buy the book']
        );
        $key->validateHeader(['alg' => JwtKey::TYPE_RSA]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function headerValidationWillFailWhenTypeIsNotJwt()
    {
        $key = new JwtKey(
            ['secret' => 'Buy the book']
        );
        $key->validateHeader(['typ' => 'Something']);
    }
}
