<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OperationObject
{
    /**
     * @var SwaggerDocument
     */
    private $document;

    /**
     * @var object
     */
    private $definition;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $method;

    /**
     * OperationObject constructor.
     *
     * @param SwaggerDocument $document
     * @param string          $path
     * @param string          $method
     */
    public function __construct(SwaggerDocument $document, string $path, string $method)
    {
        $paths = $document->getPathDefinitions();

        if (!property_exists($paths, $path)) {
            throw new \InvalidArgumentException("Path '$path' not in Swagger document");
        }
        $method = strtolower($method);
        if (!property_exists($paths->$path, $method)) {
            throw new \InvalidArgumentException("Method '$method' not supported for path '$path'");
        }

        $this->document = $document;
        $this->path = $path;
        $this->method = $method;
        $this->definition = $paths->$path->$method;
        $this->definition->{'x-request-schema'} = $this->assembleRequestSchema();
    }

    /**
     * @param \stdClass $definition
     * @param string    $path
     * @param string    $method
     *
     * @return OperationObject
     */
    public static function createFromOperationDefinition(
        \stdClass $definition,
        string $path = '/',
        string $method = 'GET'
    ): OperationObject
    {
        $method = strtolower($method);
        $documentDefinition = (object)[
            'paths' => (object)[
                $path => (object)[
                    $method => $definition
                ]
            ]
        ];
        $document = new SwaggerDocument('', $documentDefinition);

        return new static($document, $path, $method);
    }

    /**
     * @return \stdClass
     */
    public function getRequestSchema():  \stdClass
    {
        return $this->definition->{'x-request-schema'};
    }

    /**
     * @return bool
     */
    public function hasParameters(): bool
    {
        return property_exists($this->definition, 'parameters');
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->definition->parameters;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return \stdClass
     */
    public function getDefinition(): \stdClass
    {
        return $this->definition;
    }

    /**
     * @param string $parameterName
     *
     * @return string
     */
    public function createParameterPointer(string $parameterName): string
    {
        foreach ($this->definition->parameters as $i => $paramDefinition) {
            if ($paramDefinition->name === $parameterName) {
                return '/' . implode('/', [
                    'paths',
                    str_replace(['~', '/'], ['~0', '~1'], $this->getPath()),
                    $this->getMethod(),
                    'parameters',
                    $i
                ]);
            }
        }
        throw new \InvalidArgumentException("Parameter '$parameterName' not in document");
    }

    /**
     * @param string $parameterName
     *
     * @return string
     */
    public function createParameterSchemaPointer(string $parameterName): string
    {
        foreach ($this->definition->{'x-request-schema'}->properties as $propertyName => $schema) {
            if ($propertyName === $parameterName) {
                return '/' . implode('/', [
                    'paths',
                    str_replace(['~', '/'], ['~0', '~1'], $this->getPath()),
                    $this->getMethod(),
                    'x-request-schema',
                    'properties',
                    $propertyName
                ]);
            }
        }
        throw new \InvalidArgumentException("Parameter '$parameterName' not in document");
    }

    /**
     * @return \stdClass
     */
    private function assembleRequestSchema()
    {
        if (!isset($this->definition->parameters)) {
            return new \stdClass;
        }
        $schema = new \stdClass;
        $schema->type = 'object';
        $schema->required = [];
        $schema->properties = new \stdClass;

        foreach ($this->definition->parameters as $paramDefinition) {
            if (isset($paramDefinition->required) && $paramDefinition->required) {
                $schema->required[] = $paramDefinition->name;
            }
            if ($paramDefinition->in === 'body') {
                $schema->properties->{$paramDefinition->name} = property_exists($paramDefinition, 'schema')
                    ? $paramDefinition->schema
                    : (object)['type' => 'object'];
                continue;
            }

            $type = property_exists($paramDefinition, 'type') ? $paramDefinition->type : 'string';
            $propertyDefinition = $schema->properties->{$paramDefinition->name} = (object)['type' => $type];
            if (property_exists($paramDefinition, 'format')) {
                $propertyDefinition->format = $paramDefinition->format;
            }
            if (property_exists($paramDefinition, 'items')) {
                $propertyDefinition->items = $paramDefinition->items;
            }
        }

        return $schema;
    }
}
