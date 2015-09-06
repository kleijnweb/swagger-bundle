<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use JsonSchema\Exception\ResourceNotFoundException;
use JsonSchema\Uri\Retrievers\AbstractRetriever;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class YamlCapableUriRetriever extends AbstractRetriever
{
    /**
     * TODO This is of course terribly inefficient
     *
     * @see \JsonSchema\Uri\Retrievers\UriRetrieverInterface::retrieve()
     */
    public function retrieve($uri)
    {
        set_error_handler(function () use ($uri) {
            throw new ResourceNotFoundException('Schema not found at ' . $uri);
        });
        $response = file_get_contents($uri);
        restore_error_handler();

        if (false === $response) {
            throw new ResourceNotFoundException('Schema not found at ' . $uri);
        }
        if ($response == ''
            && substr($uri, 0, 7) == 'file://' && substr($uri, -1) == '/'
        ) {
            throw new ResourceNotFoundException('Schema not found at ' . $uri);
        }
        $this->contentType = null;
        if (preg_match('/\b(yml|yaml)\b/', $uri)) {
            $data = Yaml::parse($response);

            return json_encode($data);
        }
        if (!empty($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (0 < preg_match("/Content-Type:(\V*)/ims", $header, $match)) {
                    $actualContentType = trim($match[1]);
                    if (strpos($actualContentType, 'yaml')) {
                        return json_encode(Yaml::parse($response));
                    }
                }
            }
        }

        return $response;
    }
}
