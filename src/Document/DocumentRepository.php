<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use Doctrine\Common\Cache\Cache;
use KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotReadableException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DocumentRepository
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var array
     */
    private $documents = [];

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * Initializes a new Repository.
     *
     * @param string $basePath
     * @param Cache  $cache
     * @param Loader $loader
     */
    public function __construct($basePath = null, Cache $cache = null, Loader $loader = null)
    {
        $this->basePath = $basePath;
        $this->cache = $cache;
        $this->loader = $loader ?: new Loader();
    }

    /**
     * @param string $documentPath
     *
     * @return SwaggerDocument
     */
    public function get($documentPath)
    {
        if ($this->basePath) {
            $documentPath = "$this->basePath/$documentPath";
        }
        if (!$documentPath) {
            throw new \InvalidArgumentException("No document path provided");
        }
        if (!isset($this->documents[$documentPath])) {
            $this->documents[$documentPath] = $this->load($documentPath);
        }

        return $this->documents[$documentPath];
    }

    /**
     * @param string $uri
     *
     * @return SwaggerDocument
     * @throws ResourceNotReadableException
     */
    private function load($uri)
    {
        if ($this->cache && $document = $this->cache->fetch($uri)) {
            return $document;
        }

        $resolver = new RefResolver($this->loader->load($uri), $uri);
        $document = new SwaggerDocument($uri, $resolver->resolve());

        if ($this->cache) {
            $this->cache->save($uri, $document);
        }

        return $document;
    }
}
