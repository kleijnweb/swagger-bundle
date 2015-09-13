<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\Validator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RsaValidator
{
    const SHA256 = OPENSSL_ALGO_SHA256;
    const SHA512 = OPENSSL_ALGO_SHA512;

    /**
     * @var int
     */
    private $hashAlgorithm;

    /**
     * @param int $hashAlgorithm
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
        $key = openssl_pkey_get_public($secret);
        $details = openssl_pkey_get_details($key);

        if (!isset($details['key']) || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new \InvalidArgumentException('Not an RSA key');
        }

        return openssl_verify($payload, $signature, $key, $this->hashAlgorithm) === 1;
    }
}
