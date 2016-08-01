<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory;

use KleijnWeb\SwaggerBundle\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SimpleErrorResponseFactory implements ErrorResponseFactory
{
    /**
     * @param HttpError $error
     *
     * @return Response
     */
    public function create(HttpError $error): Response
    {
        return new JsonResponse(['message' => $error->getMessage()], $error->getStatusCode());
    }
}
