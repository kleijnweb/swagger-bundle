<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Serializer;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Creates a Symfony Serializer with defaults
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SymfonySerializerFactory
{
    /**
     * @param EncoderInterface $encoder
     *
     * @return Serializer
     */
    public static function factory($encoder = null)
    {
        $encoders = [$encoder ?: new JsonEncoder()];
        $normalizers = [new GetSetMethodNormalizer()];

        return new Serializer($normalizers, $encoders);
    }
}
