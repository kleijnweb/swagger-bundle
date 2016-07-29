<?php declare(strict_types = 1);
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

        $this->document                         = $document;
        $this->path                             = $path;
        $this->method                           = $method;
        $this->definition                       = $paths->$path->$method;
        $this->definition->{'x-request-schema'} = $this->assembleRequestSchema();
    }

    /**
     * @param \stdClass $definition
     * @param string    $path
     * @param string    $method
     *
     * @return static
     */
    public static function createFromOperationDefinition(
        \stdClass $definition,
        string $path = '/',
        string $method = 'GET'
    ) {

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
    public function hasParameters(): bool
    {
        return property_exists($this->definition, 'parameters');
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->hasParameters() ? $this->definition->parameters : [];
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
        $segments = explode('.', $parameterName);

        $pointer = '/'
            . implode(
                '/',
                [
                    'paths',
                    str_replace(['~', '/'], ['~0', '~1'], $this->getPath()),
                    $this->getMethod(),
                    'x-request-schema',
                    'properties'
                ]
            );

        return self::resolvePointerRecursively(
            $pointer,
            $segments,
            $this->definition->{'x-request-schema'}->properties
        );
    }

    /**
     * @param string $pointer
     * @param array  $segments
     * @param object $context
     *
     * @return mixed
     */
    public static function resolvePointerRecursively(string $pointer, array $segments, $context)
    {
        $segment = str_replace(['~0', '~1'], ['~', '/'], array_shift($segments));
        if (property_exists($context, $segment)) {
            $pointer .= '/' . $segment;
            if (!count($segments)) {
                return $pointer;
            }

            return self::resolvePointerRecursively($pointer, $segments, $context->$segment);
        }

        throw new \InvalidArgumentException("Segment '$segment' not found in context '$pointer'");
    }

    /**
     * @return \stdClass
     */
    private function assembleRequestSchema(): \stdClass
    {
        if (!isset($this->definition->parameters)) {
            return new \stdClass;
        }
        $schema             = new \stdClass;
        $schema->type       = 'object';
        $schema->required   = [];
        $schema->properties = new \stdClass;

        foreach ($this->definition->parameters as $paramDefinition) {
            $isRequired = isset($paramDefinition->required) && $paramDefinition->required;
            if ($isRequired) {
                $schema->required[] = $paramDefinition->name;
            }
            if ($paramDefinition->in === 'body') {
                $schema->properties->{$paramDefinition->name}
                    = $bodySchema = property_exists($paramDefinition, 'schema')
                    ? $paramDefinition->schema
                    : (object)['type' => 'object'];
                continue;
            }

            $propertyDefinition = clone $paramDefinition;

            // Remove non-JSON-Schema properties
            $swaggerPropertyNames = [
                'name',
                'in',
                'description',
                'required',
                'allowEmptyValue'
            ];
            foreach ($swaggerPropertyNames as $propertyName) {
                if (property_exists($propertyDefinition, $propertyName)) {
                    unset($propertyDefinition->$propertyName);
                }
            }

            $schema->properties->{$paramDefinition->name} = $propertyDefinition;
        }

        return $schema;
    }
}
