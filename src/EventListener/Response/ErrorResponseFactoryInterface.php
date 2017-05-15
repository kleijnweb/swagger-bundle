<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Response;

use KleijnWeb\SwaggerBundle\EventListener\Response\Error\HttpError;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface ErrorResponseFactoryInterface
{
    /**
     * @param HttpError $error
     *
     * @return Response
     */
    public function create(HttpError $error): Response;
}
