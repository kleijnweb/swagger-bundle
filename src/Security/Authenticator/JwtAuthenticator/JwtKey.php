<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator;

use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\SignatureValidator;
use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\HmacValidator;
use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\RsaValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JwtKey
{
    const TYPE_HMAC = 'HS256';
    const TYPE_RSA = 'RS256';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $issuer;

    /**
     * @var string
     */
    private $type = self::TYPE_HMAC;

    /**
     * @var string
     */
    private $audience;

    /**
     * @var int
     */
    private $minIssueTime;

    /**
     * @var array
     */
    private $requiredClaims = [];

    /**
     * @var int
     */
    private $issuerTimeLeeway;

    /**
     * @var string
     */
    private $secret;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (!isset($options['secret'])) {
            throw new \InvalidArgumentException("Need a secret to verify tokens");
        }
        $defaults = [
            'kid'          => null,
            'issuer'       => null,
            'audience'     => null,
            'minIssueTime' => null,
            'leeway'       => 0,
            'type'         => $this->type,
            'require'      => $this->requiredClaims,
        ];
        $options = array_merge($defaults, $options);
        $this->issuer = $options['issuer'];
        $this->audience = $options['audience'];
        $this->type = $options['type'];
        $this->minIssueTime = $options['minIssueTime'];
        $this->requiredClaims = $options['require'];
        $this->issuerTimeLeeway = $options['leeway'];
        $this->id = $options['kid'];
        $this->secret = $options['secret'];
    }

    /**
     * @param JwtToken $token
     *
     * @throws \InvalidArgumentException
     */
    public function validateToken(JwtToken $token)
    {
        $this->validateHeader($token->getHeader());
        $token->validateSignature($this->secret, $this->getSignatureValidator());
        $this->validateClaims($token->getClaims());
    }

    /**
     * @param array $header
     *
     * @throws \InvalidArgumentException
     */
    public function validateHeader(array $header)
    {
        if (!isset($header['alg'])) {
            throw new \InvalidArgumentException("Missing 'alg' in header");
        }
        if (!isset($header['typ'])) {
            throw new \InvalidArgumentException("Missing 'typ' in header");
        }
        if ($this->type !== $header['alg']) {
            throw new \InvalidArgumentException("Algorithm mismatch");
        }
    }

    /**
     * @param array $claims
     *
     * @throws \InvalidArgumentException
     */
    public function validateClaims(array $claims)
    {
        if ($this->requiredClaims) {
            $missing = array_diff_key(array_flip($this->requiredClaims), $claims);
            if (count($missing)) {
                throw new \InvalidArgumentException("Missing claims: " . implode(', ', $missing));
            }
        }
        if ($this->issuer && !isset($claims['iss'])) {
            throw new \InvalidArgumentException("Claim 'iss' is required");
        }
        if ($this->minIssueTime && !isset($claims['iat'])) {
            throw new \InvalidArgumentException("Claim 'iat' is required");
        }
        if ($this->audience && !isset($claims['aud'])) {
            throw new \InvalidArgumentException("Claim 'aud' is required");
        }
        if (!isset($claims['prn']) || empty($claims['prn'])) {
            throw new \InvalidArgumentException("Missing principle claim");
        }
        if (isset($claims['exp']) && $claims['exp'] < time()) {
            throw new \InvalidArgumentException("Token is expired by 'exp'");
        }
        if (isset($claims['iat']) && $claims['iat'] < $this->minIssueTime) {
            throw new \InvalidArgumentException("Server deemed your token too old");
        }
        if (isset($claims['nbf']) && $claims['nbf'] > time()) {
            throw new \InvalidArgumentException("Token not valid yet");
        }
        if (isset($claims['iss']) && $claims['iss'] !== $this->issuer) {
            throw new \InvalidArgumentException("Issuer mismatch");
        }
        if (isset($claims['aud']) && $claims['aud'] !== $this->audience) {
            throw new \InvalidArgumentException("Audience mismatch");
        }
    }


    /**
     * @return SignatureValidator
     */
    public function getSignatureValidator()
    {
        if ($this->type == self::TYPE_RSA) {
            return new RsaValidator();
        }

        return new HmacValidator();
    }

    /**
     * Prevent accidental persistence of secret
     */
    final public function __sleep()
    {
        return [];
    }
}
