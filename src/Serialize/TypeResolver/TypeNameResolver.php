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

class TypeNameResolver
{
    /**
     * @param \stdClass $schema
     *
     * @return bool
     */
    public function isResolvableSchema(\stdClass $schema): bool
    {
        return (bool)$this->findTypeInSchema($schema);
    }

    /**
     * @param \stdClass $schema
     *
     * @return string
     */
    public function resolveUsingSchema(\stdClass $schema): string
    {
        if ($type = $this->findTypeInSchema($schema)) {
            return $type;
        }

        throw new \InvalidArgumentException("Failed to resolve type");
    }

    /**
     * @param Operation $operationObject
     *
     * @return string
     */
    public function resolveUsingOperationBody(Operation $operationObject): string
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
    private function findTypeInSchema(\stdClass $schema): string
    {
        if (isset($schema->{'x-type'})) {
            return $schema->{'x-type'};
        }
        if (!isset($schema->type)) {
            return '';
        }

        $getTypeFromReference = function ($schema) {
            return $schema->{'x-type'} = substr($schema->{'x-ref-id'}, strrpos($schema->{'x-ref-id'}, '/') + 1);
        };

        if ($schema->type === 'array' && $schema->items->type == 'object') {
            return "{$getTypeFromReference($schema->items)}[]";
        }
        if (isset($schema->{'x-ref-id'}) && $schema->type === 'object') {
            return $getTypeFromReference($schema);
        }

        return '';
    }
}
