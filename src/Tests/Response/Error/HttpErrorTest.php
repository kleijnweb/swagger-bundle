<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\SwaggerBundle\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\Response\Error\LogRefBuilder;
use KleijnWeb\SwaggerBundle\EventListener\ExceptionListener;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class HttpErrorTest extends \PHPUnit_Framework_TestCase
{
    const LOGREF = 'abcdefghij';

    /**
     * @var LogRefBuilder
     */
    private $logRefBuilder;

    protected function setUp()
    {
        $this->logRefBuilder = $mockObject = $this->getMockForAbstractClass(LogRefBuilder::class);
        $mockObject->expects($this->any())->method('create')->willReturn(self::LOGREF);
    }

    /**
     * @test
     */
    public function willClassifyExceptionsWith4xxCodesAsBadRequestNotices()
    {
        for ($i = 0; $i < 99; $i++) {
            $error = new HttpError(new Request(), new \Exception('mimimimimimi', 400 + $i), $this->logRefBuilder);
            $this->assertSame($error->getSeverity(), LogLevel::NOTICE);
            $this->assertSame($error->getStatusCode(), 400);
            $this->assertStringStartsWith('Bad Request', $error->getMessage());
        }
    }

    /**
     * @test
     */
    public function willClassifyExceptionsWith5xxCodesAsRuntimeErrors()
    {
        for ($i = 0; $i < 99; $i++) {
            $error = new HttpError(new Request(), new \Exception('mimimimimimi', 500 + $i), $this->logRefBuilder);
            $this->assertSame($error->getSeverity(), LogLevel::ERROR);
            $this->assertSame($error->getStatusCode(), 500);
            $this->assertStringStartsWith('Internal Server Error', $error->getMessage());
        }
    }

    /**
     * @test
     */
    public function willClassifyExceptionsWithUnexpectedCodesAsCriticalErrors()
    {
        $sample = [4096, 777, 22, 5, 0];
        foreach ($sample as $code) {
            $error = new HttpError(new Request(), new \Exception('mimimimimimi', $code), $this->logRefBuilder);
            $this->assertSame($error->getSeverity(), LogLevel::CRITICAL);
            $this->assertSame($error->getStatusCode(), 500);
            $this->assertStringStartsWith('Internal Server Error', $error->getMessage());
        }
    }
}
