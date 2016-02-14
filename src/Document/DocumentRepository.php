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
     * Initializes a new Repository.
     *
     * @param string $basePath
     * @param Cache  $cache
     */
    public function __construct($basePath = null, Cache $cache = null)
    {
        $this->basePath = $basePath;
        $this->cache = $cache;
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
     * @param string $documentPath
     *
     * @return SwaggerDocument
     * @throws ResourceNotReadableException
     */
    private function load($documentPath)
    {
        if ($this->cache && $document = $this->cache->fetch($documentPath)) {
            return $document;
        }

        if (!is_readable($documentPath)) {
            throw new ResourceNotReadableException("Document '$documentPath' is not readable");
        }

        $parser = new  YamlParser();
        $resolver = new RefResolver($parser->parse((string)file_get_contents($documentPath)), $documentPath);
        $document = new SwaggerDocument($documentPath, $resolver->resolve());

        if ($this->cache) {
            $this->cache->save($documentPath, $document);
        }

        return $document;
    }
}
