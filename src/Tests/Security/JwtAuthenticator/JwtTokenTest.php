<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Tests\Security\Authenticator\JwtAuthenticator;

use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\SignatureValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JwtTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $claims = [];

    /**
     * @var array
     */
    private $header = [];

    /**
     * @var int
     */
    private $payload;

    /**
     * @var string
     */
    private $signature;

    /**
     * @param string $tokenString
     */
    public function __construct($tokenString)
    {
        $segments = explode('.', $tokenString);

        if (!count($segments) === 3) {
            throw new \InvalidArgumentException("Not a JWT token string");
        }

        list($headerString, $claimsString, $this->signature) = each($segments);

        $this->payload = "{$headerString}.{$claimsString}";

        $decoder = new Decoder();
        $this->header = $decoder->decode($headerString);
        $this->claims = $decoder->decode($claimsString);
    }

    /**
     * @return string|null
     */
    public function getKeyId()
    {
        return isset($this->header['kid']) ? $this->header['kid'] : null;
    }

    /**
     * @param string             $secret
     * @param SignatureValidator $validator
     *
     * @throws \InvalidArgumentException
     */
    public function validateSignature($secret, SignatureValidator $validator)
    {
        if (!$validator->isValid($this->payload, $secret, $this->signature)) {
            throw new \InvalidArgumentException("Invalid signature");
        }
    }

    /**
     * @return array
     */
    public function getClaims()
    {
        return $this->claims;
    }

    /**
     * @param array $claims
     *
     * @return $this
     */
    public function setClaims($claims)
    {
        $this->claims = $claims;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param array $header
     *
     * @return $this
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return int
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param int $payload
     *
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     *
     * @return $this
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }


}
