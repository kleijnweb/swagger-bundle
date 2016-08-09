<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;

interface RequestMiddleware extends Middleware
{
    public function process(Request $request): Request;
}