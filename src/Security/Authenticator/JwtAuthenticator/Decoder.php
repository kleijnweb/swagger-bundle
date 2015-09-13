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
     * @param string $data
     * @return array
     */
    public function decode($data)
    {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $plain = base64_decode(strtr($data, '-_', '+/'));

        $data = json_decode($plain, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg());
        }

        return $data;
    }
}
