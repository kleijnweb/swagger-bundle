<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Serializer;

class SerializationTypeResolver
{
    /**
     * @var string
     */
    private $resourceNamespace;

    /**
     * @param string $resourceNamespace
     */
    public function __construct($resourceNamespace = null)
    {
        $this->resourceNamespace = $resourceNamespace;
    }

    /**
     * @param object $definitionFragment
     *
     * @return null|string
     */
    public function resolve($definitionFragment)
    {
        if (isset($definitionFragment->parameters)) {
            foreach ($definitionFragment->parameters as $parameterDefinition) {
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
    public function qualify($typeName)
    {
        return ltrim($this->resourceNamespace . '\\' . $typeName, '\\');
    }

    /**
     * @param object $schema
     *
     * @return string
     */
    public function resolveUsingSchema($schema)
    {
        $reference = isset($schema->{'$ref'}) ? $schema->{'$ref'} : (isset($schema->id) ? $schema->id : null);

        if (!$reference) {
            return null;
        }

        return $this->qualify(substr($reference, strrpos($reference, '/') + 1));
    }
}
