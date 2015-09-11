<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use JsonSchema\Uri\UriRetriever;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerDocument
{
    /**
     * @var string
     */
    private $pathFileName;

    /**
     * @var \ArrayObject
     */
    private $definition;

    /**
     * @var UriRetriever
     */
    private $retriever;

    /**
     * @param $pathFileName
     */
    public function __construct($pathFileName)
    {
        if (!is_file($pathFileName)) {
            throw new \InvalidArgumentException(
                "Document file '$pathFileName' does not exist'"
            );
        }
        $this->pathFileName = $pathFileName;
        $this->retriever = new UriRetriever();
        $this->retriever->setUriRetriever(new YamlCapableUriRetriever);
        $this->definition = new \ArrayObject(
            Yaml::parse(file_get_contents($pathFileName)),
            \ArrayObject::ARRAY_AS_PROPS | \ArrayObject::STD_PROP_LIST
        );
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return array
     */
    public function getPathDefinitions()
    {
        return $this->definition->paths;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return array
     */
    public function getOperationDefinition($path, $method)
    {
        if (isset($this->definition->basePath)) {
            $path = substr($path, strlen($this->definition->basePath));
        }

        $paths = $this->getPathDefinitions();
        if (!isset($paths[$path])) {
            throw new \InvalidArgumentException("Path '$path' not in Swagger document");
        }
        $method = strtolower($method);
        if (!isset($paths[$path][$method])) {
            throw new \InvalidArgumentException("Method '$method' not supported for path '$path'");
        }

        return $paths[$path][$method];
    }

    /**
     * @param null $targetPath
     *
     * @return void
     */
    public function write($targetPath = null)
    {
        file_put_contents($targetPath ?: $this->pathFileName, Yaml::dump($this->definition->getArrayCopy(), 10, 2));
    }
}
