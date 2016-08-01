<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class TestRequestFactory
{
    /**
     * @param string|null $content
     *
     * @param array       $query
     * @param string      $specificationPath
     *
     * @return Request
     */
    public static function create($content, array $query = [], string $specificationPath = null)
    {
        $request = new Request($query, [], [], [], [], [], $content);

        if ($specificationPath) {
            $request->attributes->set('_swagger.file', $specificationPath);
        }

        return $request;
    }
}
