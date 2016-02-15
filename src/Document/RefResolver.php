<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotReadableException;
use KleijnWeb\SwaggerBundle\Document\Exception\InvalidReferenceException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RefResolver
{
    /**
     * @var object
     */
    private $definition;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * RefResolver constructor.
     *
     * @param \stdClass       $definition
     * @param string          $uri
     * @param YamlParser|null $yamlParser
     */
    public function __construct(\stdClass $definition, string $uri, YamlParser $yamlParser = null)
    {
        $this->definition = $definition;
        $uriSegs = $this->parseUri($uri);
        if (!$uriSegs['proto']) {
            $uri = realpath($uri);
        }
        $this->uri = $uri;
        $this->directory = dirname($this->uri);
        $this->yamlParser = $yamlParser ?: new YamlParser();
    }

    /**
     * @return \stdClass
     */
    public function getDefinition(): \stdClass
    {
        return $this->definition;
    }

    /**
     * Resolve all references
     *
     * @return \stdClass
     */
    public function resolve(): \stdClass
    {
        $this->resolveRecursively($this->definition);

        return $this->definition;
    }

    /**
     * Revert to original state
     *
     * @return \stdClass
     */
    public function unresolve(): \stdClass
    {
        $this->unresolveRecursively($this->definition);

        return $this->definition;
    }

    /**
     * @param \stdClass|array $current
     * @param \stdClass       $document
     * @param string          $uri
     *
     * @throws InvalidReferenceException
     * @throws ResourceNotReadableException
     */
    private function resolveRecursively(&$current, \stdClass $document = null, string $uri = null)
    {
        $document = $document ?: $this->definition;
        $uri = $uri ?: $this->uri;

        if (is_array($current)) {
            foreach ($current as &$value) {
                if ($value !== null && !is_scalar($value)) {
                    $this->resolveRecursively($value, $document, $uri);
                }
            }
        } elseif (is_object($current)) {
            if (property_exists($current, '$ref')) {
                $uri = $current->{'$ref'};
                if ('#' === $uri[0]) {
                    $current = $this->lookup($uri, $document);
                } else {
                    $uriSegs = $this->parseUri($uri);
                    $normalizedUri = $this->normalizeUri($uriSegs);
                    $externalDocument = $this->loadExternal($normalizedUri);
                    $current = $this->lookup($uriSegs['segment'], $externalDocument, $normalizedUri);
                    $this->resolveRecursively($current, $externalDocument, $normalizedUri);
                }
                if (is_object($current)) {
                    $current->id = $uri;
                    $current->{'x-ref-id'} = $uri;
                }

                return;
            }
            foreach ($current as $propertyName => &$propertyValue) {
                $this->resolveRecursively($propertyValue, $document, $uri);
            }
        }
    }

    /**
     * @param \stdClass|array $current
     * @param \stdClass|array $parent
     *
     * @return void
     */
    private function unresolveRecursively(&$current, &$parent = null)
    {
        foreach ($current as $key => &$value) {
            if ($value !== null && !is_scalar($value)) {
                $this->unresolveRecursively($value, $current);
            }
            if ($key === 'x-ref-id') {
                $parent = (object)['$ref' => $value];
            }
        }
    }

    /**
     * @param string    $path
     * @param \stdClass $document
     * @param string    $uri
     *
     * @return mixed
     * @throws InvalidReferenceException
     */
    private function lookup(string $path, \stdClass $document, string $uri = null)
    {
        $target = $this->lookupRecursively(
            explode('/', trim($path, '/#')),
            $document
        );
        if (!$target) {
            throw new InvalidReferenceException(
                "Target '$path' does not exist'" . ($uri ? " at '$uri''" : '')
            );
        }

        return $target;
    }

    /**
     * @param array     $segments
     * @param \stdClass $context
     *
     * @return mixed
     */
    private function lookupRecursively(array $segments, \stdClass $context)
    {
        $segment = str_replace(['~0', '~1'], ['~', '/'], array_shift($segments));
        if (property_exists($context, $segment)) {
            if (!count($segments)) {
                return $context->$segment;
            }

            return $this->lookupRecursively($segments, $context->$segment);
        }

        return null;
    }

    /**
     * @param string $uri
     *
     * @return \stdClass
     * @throws ResourceNotReadableException
     */
    private function loadExternal(string $uri): \stdClass
    {
        $exception = new ResourceNotReadableException("Failed reading '$uri'");

        set_error_handler(function () use ($exception) {
            throw $exception;
        });
        $response = file_get_contents($uri);
        restore_error_handler();

        if (false === $response) {
            throw $exception;
        }
        if (preg_match('/\b(yml|yaml)\b/', $uri)) {
            return $this->yamlParser->parse($response);
        }

        return json_decode($response);
    }


    /**
     * @param array $uriSegs
     *
     * @return string
     */
    private function normalizeUri(array $uriSegs): string
    {
        return
            $uriSegs['proto'] . $uriSegs['host']
            . rtrim($uriSegs['root'], '/') . '/'
            . (!$uriSegs['root'] ? ltrim("$this->directory/", '/') : '')
            . $uriSegs['path'];
    }

    /**
     * @param string $uri
     *
     * @return array
     */
    private function parseUri(string $uri): array
    {
        $defaults = [
            'root'    => '',
            'proto'   => '',
            'host'    => '',
            'path'    => '',
            'segment' => ''
        ];
        $pattern = '@'
            . '(?P<proto>[a-z]+\://)?'
            . '(?P<host>[0-9a-z\.\@\:]+\.[a-z]+)?'
            . '(?P<root>/)?'
            . '(?P<path>[^#]*)'
            . '(?P<segment>#.*)?'
            . '@';

        preg_match($pattern, $uri, $matches);

        return array_merge($defaults, array_intersect_key($matches, $defaults));
    }
}
