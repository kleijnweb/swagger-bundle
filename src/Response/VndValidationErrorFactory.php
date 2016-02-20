<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response;

use KleijnWeb\SwaggerBundle\Document\ParameterRefBuilder;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use Nocarrier\Hal;
use Ramsey\VndError\VndError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndValidationErrorFactory
{
    const DEFAULT_MESSAGE = 'Input Validation Failure';

    /**
     * @var ParameterRefBuilder
     */
    private $refBuilder;

    /**
     * @param ParameterRefBuilder $refBuilder
     */
    public function __construct(ParameterRefBuilder $refBuilder)
    {
        $this->refBuilder = $refBuilder;
    }

    /**
     * @param Request                    $request
     * @param InvalidParametersException $exception
     * @param string|null                $logRef
     *
     * @return VndError
     */
    public function create(Request $request, InvalidParametersException $exception, $logRef = null)
    {
        $error = new VndError(self::DEFAULT_MESSAGE, $logRef);
        $error->addLink('about', $this->refBuilder->buildDocumentLink($request), ['title' => 'Api Specification']);
        $error->setUri($request->getUri());

        foreach ($exception->getValidationErrors() as $errorSpec) {
            // For older versions, try to extract the property name from the message
            if (!$errorSpec['property']) {
                $errorSpec['property'] = preg_replace('/the property (.*) is required/', '\\1', $errorSpec['message']);
            }
            $normalizedPropertyName = preg_replace('/\[\d+\]/', '', $errorSpec['property']);
            $data = [
                'message' => $errorSpec['message'],
                'path'    => $this->refBuilder->createParameterSchemaPointer($request, $normalizedPropertyName)
            ];
            $parameterDefinitionUri = $this->refBuilder->buildSpecificationLink($request, $normalizedPropertyName);

            $validationError = new Hal($parameterDefinitionUri, $data);
            $error->addResource(
                'errors',
                $validationError
            );
        }

        return $error;
    }
}
