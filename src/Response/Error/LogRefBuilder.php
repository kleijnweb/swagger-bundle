<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response\Error;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface LogRefBuilder
{
    /**
     * @param Request    $request
     * @param \Exception $exception
     *
     * @return string
     */
    public function create(Request $request, \Exception $exception): string;
}

 class CustomLogRefBuilder implements LogRefBuilder
 {
     public function create(Request $request, \Exception $exception): string
     {
         return uniqid("{$request->headers->get('x-request-id')}_{$exception->getCode()}_");
     }
 }