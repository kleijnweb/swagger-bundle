<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Serialize\TypeResolver;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Document\Specification\Operation;

class ClassNameResolver
{
    /**
     * @var array
     */
    private $resourceNamespaces = [];

    /**
     * @var TypeNameResolver
     */
    private $typeNameResolver;

    /**
     * TypeNameResolver constructor.
     *
     * @param array            $resourceNamespaces
     * @param TypeNameResolver $typeNameResolver
     */
    public function __construct(array $resourceNamespaces, TypeNameResolver $typeNameResolver)
    {
        $this->resourceNamespaces = $resourceNamespaces;
        $this->typeNameResolver   = $typeNameResolver;
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    public function resolve(string $typeName): string
    {
        foreach ($this->resourceNamespaces as $resourceNamespace) {
            $suffix = "";
            if (false !== strpos($typeName, '[]')) {
                $typeName = substr($typeName, 0, strlen($typeName) - 2);
                $suffix = "[]";
            }
            if (class_exists($fqcn = $this->qualify($resourceNamespace, $typeName))) {
                return $fqcn . $suffix;
            }
        }

        throw new \InvalidArgumentException("Failed to resolve type '$typeName' to a class name");
    }

    /**
     * @param string $resourceNamespace
     * @param string $typeName
     *
     * @return string
     */
    protected function qualify(string $resourceNamespace, string $typeName): string
    {
        return ltrim($resourceNamespace . '\\' . $typeName, '\\');
    }
}
