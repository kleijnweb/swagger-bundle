<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerDocument
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var object
     */
    private $definition;

    /**
     * @param string $pathFileName
     * @param object $definition
     */
    public function __construct($pathFileName, $definition)
    {
        $this->uri = $pathFileName;
        $this->definition = $definition;
    }

    /**
     * @return object
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return object
     */
    public function getPathDefinitions()
    {
        return $this->definition->paths;
    }

    /**
     * @return object
     */
    public function getResourceSchemas()
    {
        return $this->definition->definitions;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->definition->basePath;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return object
     */
    public function getOperationDefinition($path, $method)
    {
        $paths = $this->getPathDefinitions();
        if (!property_exists($paths, $path)) {
            throw new \InvalidArgumentException("Path '$path' not in Swagger document");
        }
        $method = strtolower($method);
        if (!property_exists($paths->$path, $method)) {
            throw new \InvalidArgumentException("Method '$method' not supported for path '$path'");
        }

        return $paths->$path->$method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}
