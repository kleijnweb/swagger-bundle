<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Serialize\TypeResolver;

use KleijnWeb\SwaggerBundle\Document\Specification;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SerializerTypeDefinitionMap
{
    /**
     * @var array
     */
    private $typeNames = [];

    /**
     * @var array
     */
    private $definitions;

    /**
     * SerializerTypeDefinitionMap constructor.
     *
     * @param array $typeNames
     * @param array $definitions
     */
    public function __construct(array $definitions, $typeNames)
    {
        $this->typeNames   = $typeNames;
        $this->definitions = $definitions;
    }

    /**
     * @param string $operationId
     *
     * @return \stdClass
     */
    public function getDefinitionByOperationId(string $operationId): \stdClass
    {
        if (!isset($this->definitions["_op_$operationId"])) {
            throw new \InvalidArgumentException("Operation '$operationId' not in definition map");
        }

        return $this->definitions["_op_$operationId"];
    }

    /**
     * @param string $type
     *
     * @return \stdClass
     */
    public function getDefinitionByType(string $type): \stdClass
    {
        if (!isset($this->definitions[$type])) {
            throw new \InvalidArgumentException("Type '$type' not in definition map");
        }

        return $this->definitions[$type];
    }

    /**
     * @param string $fqdn
     *
     * @return \stdClass
     */
    public function getDefinitionByFqcn(string $fqdn): \stdClass
    {
        $type = $this->getType($fqdn);

        if (!isset($this->definitions[$type])) {
            throw new \InvalidArgumentException("Type '$type' not in definition map");
        }

        return $this->definitions[$type];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getFqcn(string $type): string
    {
        $lookup = [];
        foreach ($this->typeNames as $key => $typeName) {
            if (0 === strpos($key, "_op_")) {
                continue;
            }
            $lookup[$typeName] = $key;
        }

        if (!isset($lookup[$type])) {
            throw new \InvalidArgumentException("Class '$type' not in type map");
        }

        return $lookup[$type];
    }

    /**
     * @param string $operationId
     *
     * @return string
     */
    public function getTypeNameByOperationId(string $operationId): string
    {
        if (!isset($this->typeNames["_op_$operationId"])) {
            throw new \InvalidArgumentException("Operation '$operationId' not in type map");
        }

        return $this->typeNames["_op_$operationId"];
    }

    /**
     * @param string $fqdn
     *
     * @return string
     */
    private function getType(string $fqdn): string
    {
        if (!isset($this->typeNames[$fqdn])) {
            throw new \InvalidArgumentException("Type '$fqdn' not in type map");
        }

        return $this->typeNames[$fqdn];
    }
}
