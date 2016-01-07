<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerDocument
{
    /**
     * @var string
     */
    private $pathFileName;

    /**
     * @var \ArrayObject
     */
    private $definition;

    /**
     * @param $pathFileName
     */
    public function __construct($pathFileName)
    {
        if (!is_file($pathFileName)) {
            throw new \InvalidArgumentException(
                "Document file '$pathFileName' does not exist'"
            );
        }

        $data = Yaml::parse(file_get_contents($pathFileName));
        $data = self::resolveSelfReferences($data, $data);

        $this->pathFileName = $pathFileName;
        $this->definition = new \ArrayObject($data, \ArrayObject::ARRAY_AS_PROPS | \ArrayObject::STD_PROP_LIST);
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return array
     */
    public function getPathDefinitions()
    {
        return $this->definition->paths;
    }

    /**
     * @return array
     */
    public function getResourceSchemas()
    {
        return $this->definition->definitions;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->definition->basePath;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return array
     */
    public function getOperationDefinition($path, $method)
    {
        $paths = $this->getPathDefinitions();
        if (!isset($paths[$path])) {
            throw new \InvalidArgumentException("Path '$path' not in Swagger document");
        }
        $method = strtolower($method);
        if (!isset($paths[$path][$method])) {
            throw new \InvalidArgumentException("Method '$method' not supported for path '$path'");
        }

        return $paths[$path][$method];
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->definition->getArrayCopy();
    }

    /**
     * @param null $targetPath
     *
     * @return void
     */
    public function write($targetPath = null)
    {
        $data = $this->getArrayCopy();
        $data = self::unresolveSelfReferences($data, $data);
        $yaml = Yaml::dump($data, 10, 2);
        $yaml = str_replace(': {  }', ': []', $yaml);
        file_put_contents($targetPath ?: $this->pathFileName, $yaml);
    }

    /**
     * Cloning will break things
     */
    private function __clone()
    {
    }

    /**
     * @param array $segments
     * @param array $context
     *
     * @return mixed
     */
    private static function lookupUsingSegments(array $segments, array $context)
    {
        $segment = array_shift($segments);
        if (isset($context[$segment])) {
            if (!count($segments)) {
                return $context[$segment];
            }

            return self::lookupUsingSegments($segments, $context[$segment]);
        }

        return null;
    }

    /**
     * @param array $doc
     * @param array $data
     *
     * @return array
     */
    private function resolveSelfReferences(array $doc, array &$data)
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::resolveSelfReferences($doc, $value);
            }
            if ($key === '$ref' && '#' === $value[0]) {
                $data = self::lookupUsingSegments(
                    explode('/', trim(substr($value, 1), '/')),
                    $doc
                );
                $data['id'] = $value;
                // Use something a little less generic for more reliable qnd restoring of original
                $data['x-swagger-id'] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array $doc
     * @param array $data
     *
     * @return array
     */
    private function unresolveSelfReferences(array $doc, array &$data)
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::unresolveSelfReferences($doc, $value);
            }
            if ($key === 'x-swagger-id') {
                $data = ['$ref' => $value];
            }
        }

        return $data;
    }
}
