<?php


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

        $this->assertEquals('400',$exception->getCode());
    }
}