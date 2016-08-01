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
     * @param callable $f
     *
     * @return void
     */
    public function apply(callable  $f)
    {
        $recurse = function (&$item) use ($f, &$recurse) {

            foreach ($item as $attribute => &$value) {
                if (false === $f($value, $attribute)) {
                    return false;
                }
                if ($value === null) {
                    return true;
                }
                if (!is_scalar($value)) {
                    if (false === $recurse($value)) {
                        return false;
                    }
                }
            }

            return true;
        };
        $recurse($this->definition);
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
        $operation = new Operation($this, $path, $method);

        if (isset($this->operations[$operation->getId()])) {
            return $this->operations[$operation->getId()];
        }

        return $this->operations[$operation->getId()] = $operation;
    }

    /**
     * @return Operation[]
     */
    public function getOperations(): array
    {
        $operations = [];

        foreach ($this->getPaths() as $path => $pathItem) {
            foreach (array_keys((array)$pathItem) as $method) {
                $operations[] = $this->getOperation($path, $method);
            }
        }

        return $operations;
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
