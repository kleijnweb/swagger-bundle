<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Exception;

use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use PHPUnit_Framework_TestCase;

class MalformedContentExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function malformedContentWillThrow400()
    {
        $exception = new MalformedContentException();

        $this->assertEquals('400', $exception->getCode());
    }
}
