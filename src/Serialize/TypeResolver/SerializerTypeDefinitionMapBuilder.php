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

class SerializerTypeDefinitionMapBuilder
{
    /**
     * @var TypeNameResolver
     */
    private $typeNameResolver;

    /**
     * @var ClassNameResolver
     */
    private $classNameResolver;

    /**
     * SerializerTypeDefinitionMapBuilder constructor.
     *
     * @param TypeNameResolver  $typeNameResolver
     * @param ClassNameResolver $classNameResolver
     */
    public function __construct(TypeNameResolver $typeNameResolver, ClassNameResolver $classNameResolver)
    {
        $this->typeNameResolver  = $typeNameResolver;
        $this->classNameResolver = $classNameResolver;
    }

    /**
     * @param Specification $specification
     *
     * @return SerializerTypeDefinitionMap
     */
    public function build(Specification $specification): SerializerTypeDefinitionMap
    {
        $typeNames   = [];
        $definitions = [];

        $add = function ($schema, $key, $typeName, $operationId = null) use (&$typeNames, &$definitions) {
            if (!isset($schema->{'x-class'})) {
                $schema->{'x-class'} = $this->classNameResolver->resolve($typeName);
            }
            $definitions[$key]               = $schema;
            $typeNames[$schema->{'x-class'}] = $typeName;
            if ($operationId) {
                $typeNames["_op_$operationId"] = $typeName;
            }
        };

        $specification->apply(function (&$schema) use ($add) {
            if ($schema instanceof \stdClass) {
                if ($this->typeNameResolver->isResolvableSchema($schema)) {
                    $typeName = $key = $this->typeNameResolver->resolveUsingSchema($schema);
                    $add($schema, $key, $typeName);
                }
            }
        });

        $operations = $specification->getOperations();
        array_walk($operations, function (Operation $operation) use ($add) {
            if ($operation->hasParameters()) {
                foreach ($operation->getParameters() as $parameterDefinition) {
                    if ($parameterDefinition->in == 'body' && isset($parameterDefinition->schema)) {
                        $typeName = $this->typeNameResolver->resolveUsingSchema($parameterDefinition->schema);
                        $add($parameterDefinition->schema, "_op_{$operation->getId()}", $typeName, $operation->getId());
                    }
                }
            }
        });

        return new SerializerTypeDefinitionMap($definitions, $typeNames);
    }
}
