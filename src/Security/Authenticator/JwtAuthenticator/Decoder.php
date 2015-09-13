<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Decoder
{
    /**
     * @param string $base64Encoded
     *
     * @return array
     */
    public function decode($base64Encoded)
    {
        $this->jsonDecode($this->base64Decode($base64Encoded));
    }

    /**
     * @param string $plain
     *
     * @return array
     */
    public function jsonDecode($plain)
    {
        $data = json_decode($plain, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg());
        }

        return $data;
    }

    /**
     * @param string $base64Encoded
     *
     * @return array
     */
    public function base64Decode($base64Encoded)
    {
        if ($remainder = strlen($base64Encoded) % 4) {
            $base64Encoded .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($base64Encoded, '-_', '+/'));
    }
}
