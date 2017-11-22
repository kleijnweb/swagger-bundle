<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Response\Error;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface LogRefBuilderInterface
{
    /**
     * @param Request    $request
     * @param \Exception $exception
     *
     * @return string
     */
    public function create(Request $request, \Exception $exception): string;
}
