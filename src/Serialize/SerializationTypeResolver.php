<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Serialize;

use KleijnWeb\SwaggerBundle\Document\Specification\Operation;

class SerializationTypeResolver
{
    /**
     * @var array
     */
    private $resourceNamespaces = [];

    /**
     * @var array
     */
    private $lookupTable = [];

    /**
     * SerializationTypeResolver constructor.
     *
     * @param array $resourceNamespaces
     */
    public function __construct(array $resourceNamespaces)
    {
        $this->resourceNamespaces = $resourceNamespaces;
    }

    /**
     * @param Operation $operationObject
     *
     * @return string
     */
    public function resolveOperationBodyType(Operation $operationObject): string
    {
        if ($operationObject->hasParameters()) {
            foreach ($operationObject->getParameters() as $parameterDefinition) {
                if ($parameterDefinition->in == 'body' && isset($parameterDefinition->schema)) {
                    return $this->resolveUsingSchema($parameterDefinition->schema);
                }
            }
        }

        throw new \InvalidArgumentException("Failed to resolve type");
    }

    /**
     * @param \stdClass $schema
     *
     * @return string
     */
    public function resolveUsingSchema(\stdClass $schema): string
    {
        $reference = isset($schema->{'$ref'})
            ? $schema->{'$ref'}
            : (isset($schema->{'x-ref-id'}) ? $schema->{'x-ref-id'} : null);

        if ($reference) {
            return $this->resolveUsingTypeName(
                substr($reference, strrpos($reference, '/') + 1)
            );
        }

        throw new \InvalidArgumentException("Failed to resolve type using schema");
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    public function resolveUsingTypeName(string $typeName): string
    {
        if (isset($this->lookupTable[$typeName])) {
            return $this->lookupTable[$typeName];
        }

        foreach ($this->resourceNamespaces as $resourceNamespace) {
            $resourceFullNamespacedName = $this->qualify($resourceNamespace, $typeName);
            if (class_exists($resourceFullNamespacedName)) {
                $this->lookupTable[$typeName] = $resourceFullNamespacedName;

                return $resourceFullNamespacedName;
            }
        }

        throw new \InvalidArgumentException("Failed to resolve type '$typeName' to a class name");
    }

    /**
     * @param string $resourceFullNamespacedName
     *
     * @return string
     */
    public function reverseLookup(string $resourceFullNamespacedName): string
    {
        $table = array_flip($this->lookupTable);

        if (isset($table[$resourceFullNamespacedName])) {
            return $table[$resourceFullNamespacedName];
        }

        throw new \InvalidArgumentException("Unknown class '$resourceFullNamespacedName' or not resolved yet");

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
