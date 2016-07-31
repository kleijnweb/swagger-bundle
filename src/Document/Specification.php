<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use KleijnWeb\SwaggerBundle\Document\Specification\Operation;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Specification
{
    /**
     * @var object
     */
    private $definition;

    /**
     * @var Operation[]
     */
    private $operations;

    /**
     * @param \stdClass $definition
     */
    public function __construct(\stdClass $definition)
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
    public function getPaths(): \stdClass
    {
        return $this->definition->paths;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return Operation
     */
    public function getOperation(string $path, string $method): Operation
    {
        $key = "$path::$method";

        if (isset($this->operations[$key])) {
            return $this->operations[$key];
        }

        return $this->operations[$key] = new Operation($this, $path, $method);
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
        return $this->getOperation($path, $method)->getDefinition();
    }
}
