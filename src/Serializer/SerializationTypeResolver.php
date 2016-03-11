<?php
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
     * @var array
     */
    private $resourceNamespaces = array();

    /**
     * @param mixed $resourceNamespaces
     */
    public function __construct($resourceNamespaces = null)
    {
        if(!is_array($resourceNamespaces)) {
            $resourceNamespaces = [$resourceNamespaces];
        }
        $this->resourceNamespaces = $resourceNamespaces;
    }

    /**
     * @param OperationObject $operationObject
     *
     * @return null|string
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

        return null;
    }

    /**
     * @param string $typeName
     *
     * @return string
     */
    protected function qualify($resourceNamespace, $typeName)
    {
        return ltrim($resourceNamespace . '\\' . $typeName, '\\');
    }

    /**
     * @param object $schema
     *
     * @return string
     */
    public function resolveUsingSchema($schema)
    {
        $reference = isset($schema->{'$ref'})
            ? $schema->{'$ref'}
            : (isset($schema->{'x-ref-id'}) ? $schema->{'x-ref-id'} : null)
        ;

        if ($reference) {
            $reference = substr($reference, strrpos($reference, '/') + 1);

            foreach ($this->resourceNamespaces as $resourceNamespace) {
                $resourceFullNamespace = $this->qualify($resourceNamespace, $reference);
                if (class_exists($resourceFullNamespace)) {
                    return $resourceFullNamespace;
                }
            }
        }

        return null;
    }
}
