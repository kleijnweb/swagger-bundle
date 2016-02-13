<?php
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
     * Construct the wrapper
     *
     * @param string      $basePath
     * @param string|null $scheme
     * @param string|null $host
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
    public function buildLink(Request $request, $parameterName)
    {
        /** @var SwaggerDocument $document */
        $document = $request->attributes->get('_swagger_document');
        /** @var OperationObject $operation */
        $operation = $request->attributes->get('_swagger_operation');
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
        $pointer = $operation->createParameterPointer($parameterName);

        return "$scheme://$host{$basePath}/{$filePath}#$pointer";
    }
}
