<?php
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
    private $document;

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
     * @param object     $document
     * @param string     $uri
     * @param YamlParser $yamlParser
     */
    public function __construct($document, $uri, YamlParser $yamlParser = null)
    {
        if (!is_object($document)) {
            throw new \InvalidArgumentException("Document must be object");
        }

        $this->document = $document;
        $uriSegs = $this->parseUri($uri);
        if (!$uriSegs['proto']) {
            $uri = realpath($uri);
        }
        $this->uri = $uri;
        $this->directory = dirname($this->uri);
        $this->yamlParser = $yamlParser ?: new YamlParser();
    }

    /**
     * @return object
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Resolve all references
     *
     * @return object
     */
    public function resolve()
    {
        $this->resolveRecursively($this->document);

        return $this->document;
    }

    /**
     * Revert to original state
     */
    public function unresolve()
    {
        $this->unresolveRecursively($this->document, $this->document);
    }

    /**
     * @param object|array $composite
     * @param object       $document
     * @param string       $uri
     *
     * @throws InvalidReferenceException
     * @throws ResourceNotReadableException
     */
    private function resolveRecursively(&$composite, $document = null, $uri = null)
    {
        $document = $document ?: $this->document;
        $uri = $uri ?: $this->uri;

        if (is_array($composite)) {
            foreach ($composite as &$value) {
                if (!is_scalar($value)) {
                    $this->resolveRecursively($value, $document, $uri);
                }
            }
        } elseif (is_object($composite)) {
            if (property_exists($composite, '$ref')) {
                $uri = $composite->{'$ref'};
                if ('#' === $uri[0]) {
                    $composite = $this->lookup($uri, $document, $uri);
                } else {
                    $uriSegs = $this->parseUri($uri);
                    $normalizedUri = $this->normalizeUri($uriSegs);
                    $externalDocument = $this->loadExternal($normalizedUri);
                    $composite = $this->lookup($uriSegs['segment'], $externalDocument, $normalizedUri);
                    $this->resolveRecursively($composite, $externalDocument, $normalizedUri);
                }

                $composite->id = $uri;
                $composite->{'x-ref-id'} = $uri;

                return;
            }
            foreach ($composite as $propertyName => &$propertyValue) {
                $this->resolveRecursively($propertyValue, $document, $uri);
            }
        }
    }

    /**
     * @param object $current
     * @param object $parent
     *
     * @return void
     */
    private function unresolveRecursively($current, &$parent = null)
    {
        foreach ($current as $key => &$value) {
            if (is_object($value)) {
                $this->unresolveRecursively($value, $current);
            }
            if ($key === 'x-ref-id') {
                $parent = (object)['$ref' => $value];
            }
        }
    }

    /**
     * @param string $path
     * @param object $document
     * @param string $uri
     *
     * @return mixed
     * @throws InvalidReferenceException
     */
    private function lookup($path, $document, $uri)
    {
        $target = $this->lookupRecursively(
            explode('/', trim($path, '/#')),
            $document
        );
        if (!$target) {
            throw new InvalidReferenceException("Target '$path' does not exist' at '$uri''");
        }

        return $target;
    }

    /**
     * @param array  $segments
     * @param object $context
     *
     * @return mixed
     */
    private function lookupRecursively(array $segments, $context)
    {
        $segment = array_shift($segments);
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
     * @return object
     * @throws ResourceNotReadableException
     */
    private function loadExternal($uri)
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
    private function normalizeUri(array $uriSegs)
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
    private function parseUri($uri)
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
