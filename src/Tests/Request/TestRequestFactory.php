<?php
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
class TestRequestFactory extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string|null $content
     *
     * @param array       $query
     *
     * @return Request
     */
    public static function create($content, array $query = [])
    {
        return new Request($query, [], [], [], [], [], $content);
    }
}
