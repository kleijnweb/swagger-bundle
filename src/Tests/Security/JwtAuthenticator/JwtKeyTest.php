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
