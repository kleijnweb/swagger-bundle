<?php
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
     * @param SwaggerDocument $document
     * @param string          $path
     * @param string          $method
     */
    public function __construct(SwaggerDocument $document, $path, $method)
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
     * @param object $definition
     * @param string $path
     * @param string $method
     *
     * @return static
     */
    public static function createFromOperationDefinition($definition, $path = '/', $method = 'GET')
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
     * @return object
     */
    public function getRequestSchema()
    {
        return $this->definition->{'x-request-schema'};
    }

    /**
     * @return bool
     */
    public function hasParameters()
    {
        return property_exists($this->definition, 'parameters');
    }

    /**
     * @return object
     */
    public function getParameters()
    {
        return $this->definition->parameters;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return object
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param string $parameterName
     *
     * @return string
     */
    public function createParameterPointer($parameterName)
    {
        foreach ($this->definition->parameters as $i => $paramDefinition) {
            if ($paramDefinition->name === $parameterName) {
                return '/' . implode('/', [
                    'paths',
                    str_replace(['~', '/'], ['~0', '~1'], $parameterName),
                    'post',
                    'parameters',
                    $i
                ]);
            }
        }
        throw new \InvalidArgumentException("Parameter '$parameterName' not in document");
    }

    /**
     * @return object
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
            $schema->properties->{$paramDefinition->name} = (object)['type' => $type];
        }

        return $schema;
    }
}
