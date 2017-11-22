<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Test;

use KleijnWeb\SwaggerBundle\Test\ApiRequest;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiRequestTest extends TestCase
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

        $this->assertTrue(true, "Just testing the interface");
    }
}
