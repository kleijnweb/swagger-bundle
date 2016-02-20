<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterRefBuilder
{
    /**
     * @var array
     */
    private static $schemes = ['https', 'wss', 'http', 'ws'];

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $basePath;

    /**
     * ParameterRefBuilder constructor.
     *
     * @param string $basePath
     * @param null   $scheme
     * @param null   $host
     */
    public function __construct($basePath = '/', $scheme = null, $host = null)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->basePath = $basePath;
    }

    /**
     * @param Request $request
     * @param string  $parameterName
     *
     * @return string
     */
    public function buildSpecificationLink(Request $request, string $parameterName): string
    {
        return "{$this->buildDocumentLink($request)}#{$this->createParameterPointer($request, $parameterName)}";
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function buildDocumentLink(Request $request): string
    {
        /** @var SwaggerDocument $document */
        $document = $request->attributes->get('_swagger_document');
        /** @var string $filePath */
        $filePath = $request->attributes->get('_definition');

        $definition = $document->getDefinition();
        $basePath = $this->basePath;
        $host = $this->host ?: property_exists($definition, 'host') ? $definition->host : $request->getHost();
        $scheme = $this->scheme;
        if (!$scheme) {
            $scheme = $request->getScheme();
            if (property_exists($definition, 'schemes')) {
                if (!in_array($scheme, self::$schemes)) {
                    foreach (self::$schemes as $knownScheme) {
                        if (in_array($knownScheme, $definition->schemes)) {
                            $this->scheme = $knownScheme;
                            break;
                        }
                    }
                }
            }
        }

        return "$scheme://$host{$basePath}{$filePath}";
    }

    /**
     * @param Request $request
     * @param string  $parameterName
     *
     * @return string
     */
    public function createParameterPointer(Request $request, string $parameterName): string
    {
        /** @var OperationObject $operation */
        $operation = $request->attributes->get('_swagger_operation');

        return $operation->createParameterPointer($parameterName);
    }

    /**
     * @param Request $request
     * @param string  $parameterName
     *
     * @return string
     */
    public function createParameterSchemaPointer(Request $request, string $parameterName): string
    {
        /** @var OperationObject $operation */
        $operation = $request->attributes->get('_swagger_operation');

        return $operation->createParameterSchemaPointer($parameterName);
    }
}
