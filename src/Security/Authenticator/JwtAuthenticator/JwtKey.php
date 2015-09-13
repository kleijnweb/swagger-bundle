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
        $token->validateSignature($this->secret, $this->getSignatureValidator());

        $this->validateHeader($token->getHeader());
        $this->validateClaims($token->getClaims());
    }

    /**
     * @param array $header
     */
    public function validateHeader(array $header)
    {
        throw new \InvalidArgumentException("Invalid header");
    }

    /**
     * @param array $header
     */
    public function validateClaims(array $header)
    {
        throw new \InvalidArgumentException("Invalid claims");
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
        $this->secret = null;
    }
}
