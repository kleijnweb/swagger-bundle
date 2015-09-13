<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class HmacValidator
{
    const SHA256 = 'sha256';
    const SHA512 = 'sha512';

    /**
     * @var string
     */
    private $hashAlgorithm;

    /**
     * @param string $hashAlgorithm
     */
    public function __construct($hashAlgorithm = self::SHA256)
    {
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * @param string $payload
     * @param string $secret
     * @param string $signature
     *
     * @return bool
     */
    public function isValid($payload, $secret, $signature)
    {
        $actual = hash_hmac($this->hashAlgorithm, $payload, $secret);
        return $signature === $actual;
    }
}