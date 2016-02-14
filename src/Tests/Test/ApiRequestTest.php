<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Test;

use KleijnWeb\SwaggerBundle\Test\ApiRequest;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canChain()
    {
        (new ApiRequest('/', 'POST'))
            ->setContent('')
            ->setCookies([])
            ->setFiles([])
            ->setMethod('POST')
            ->setParameters([])
            ->setServer([])
            ->setUri('/');
    }
}
