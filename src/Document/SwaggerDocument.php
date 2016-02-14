<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

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
     * @var OperationObject[]
     */
    private $operations;

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
     * @param string $path
     * @param string $method
     *
     * @return OperationObject
     */
    public function getOperationObject($path, $method)
    {
        $key = "$path::$method";

        if (isset($this->operations[$key])) {
            return $this->operations[$key];
        }

        return $this->operations[$key] = new OperationObject($this, $path, $method);
    }

    /**
     * @deprecated
     *
     * @param string $path
     * @param string $method
     *
     * @return object
     */
    public function getOperationDefinition($path, $method)
    {
        return $this->getOperationObject($path, $method)->getDefinition();
    }
}
