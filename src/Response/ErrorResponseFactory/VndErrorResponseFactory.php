<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory;

use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory\VndError\VndErrorResponse;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory\VndError\VndValidationErrorFactory;
use Ramsey\VndError\VndError;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndErrorResponseFactory implements ErrorResponseFactory
{
    /**
     * @var VndValidationErrorFactory;
     */
    private $vndValidationErrorFactory;

    /**
     * VndValidationErrorFactory constructor.
     *
     * @param VndValidationErrorFactory $vndValidationErrorFactory
     */
    public function __construct(VndValidationErrorFactory $vndValidationErrorFactory)
    {
        $this->vndValidationErrorFactory = $vndValidationErrorFactory;
    }

    /**
     * @param HttpError $error
     *
     * @return Response
     */
    public function create(HttpError $error): Response
    {
        $exception = $error->getException();
        if (!$exception instanceof InvalidParametersException) {
            return $this->createResponseWithError($error, new VndError($error->getMessage(), $error->getLogRef()));
        }

        return $this->createResponseWithError(
            $error,
            $this->vndValidationErrorFactory->create(
                $error->getRequest(),
                $exception,
                $error->getLogRef()
            )
        );
    }

    /**
     * @param HttpError $httpError
     * @param VndError  $vndError
     *
     * @return VndErrorResponse
     */
    private function createResponseWithError(HttpError $httpError, VndError $vndError): VndErrorResponse
    {
        return new VndErrorResponse($vndError, $httpError->getStatusCode());
    }
}
