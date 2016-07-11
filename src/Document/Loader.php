<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotDecodableException;
use KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotReadableException;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Loader
{
    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * @param YamlParser $yamlParser
     */
    public function __construct(YamlParser $yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?: new YamlParser();
    }

    /**
     * @param string $uri
     *
     * @return \stdClass
     * @throws ResourceNotDecodableException
     * @throws ResourceNotReadableException
     */
    public function load($uri)
    {
        $exception = new ResourceNotReadableException("Failed reading '$uri'");
        $response = @file_get_contents($uri);

        if (false === $response) {
            throw $exception;
        }
        if (preg_match('/\b(yml|yaml)\b/', $uri)) {
            try {
                $content = $this->yamlParser->parse($response);
            } catch (ParseException $e) {
                throw new ResourceNotDecodableException("Failed to parse '$uri' as YAML", 0, $e);
            }

            return $content;
        }
        if (!$content = json_decode($response)) {
            throw new ResourceNotDecodableException("Failed to parse '$uri' as JSON");
        }

        return $content;
    }
}
