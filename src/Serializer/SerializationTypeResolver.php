<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Serializer;

use KleijnWeb\SwaggerBundle\Document\OperationObject;

class SerializationTypeResolver
{
    /**
     * @var string
     */
    private $resourceNamespace;

    /**
     * @param string $resourceNamespace
     */
    public function __construct(string $resourceNamespace = null)
    {
        $this->resourceNamespace = $resourceNamespace;
    }

    /**
     * @param OperationObject $operationObject
     *
     * @return string
     */
    public function resolve(OperationObject $operationObject)
    {
        if ($operationObject->hasParameters()) {
            foreach ($operationObject->getParameters() as $parameterDefinition) {
                if ($parameterDefinition->in == 'body' && isset($parameterDefinition->schema)) {
                    return $this->resolveUsingSchema($parameterDefinition->schema);
                }
            }
        }

        return '';
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    public function qualify(string $typeName): string
    {
        return ltrim($this->resourceNamespace . '\\' . $typeName, '\\');
    }

    /**
     * @param \stdClass $schema
     *
     * @return string
     */
    public function resolveUsingSchema(\stdClass $schema): string
    {
        $reference = $schema->{'$ref'} ?? $schema->id ?? null;

        if (!$reference) {
            return '';
        }

        return $this->qualify(substr($reference, strrpos($reference, '/') + 1));
    }
}
