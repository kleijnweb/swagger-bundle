<?php declare(strict_types = 1);
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
     * @var object
     */
    private $definition;

    /**
     * @var OperationObject[]
     */
    private $operations;

    /**
     * @param string    $pathFileName
     * @param \stdClass $definition
     */
    public function __construct(string $pathFileName, \stdClass $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return \stdClass
     */
    public function getDefinition(): \stdClass
    {
        return $this->definition;
    }

    /**
     * @return \stdClass
     */
    public function getPathDefinitions(): \stdClass
    {
        return $this->definition->paths;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return OperationObject
     */
    public function getOperationObject(string $path, string $method): OperationObject
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
     * @return \stdClass
     */
    public function getOperationDefinition(string $path, string $method): \stdClass
    {
        return $this->getOperationObject($path, $method)->getDefinition();
    }
}
