<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\SwaggerBundle\EventListener\ExceptionListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GetResponseForExceptionEvent
     */
    private $event;

    /**
     * @var \ReflectionProperty
     */
    private $codeProperty;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var ExceptionListener
     */
    private $exceptionListener;

    /**
     * Set up mocking
     */
    protected function setUp()
    {
        $this->exception = new \Exception("Mary had a little lamb");
        $reflection = new \ReflectionClass($this->exception);
        $codeProperty = $reflection->getProperty('code');
        $this->codeProperty = $codeProperty;
        $this->codeProperty->setAccessible(true);

        $this->event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getException'])
            ->getMock();

        $this->event->expects($this->any())
            ->method('getException')
            ->willReturn($this->exception);

        /** @var LoggerInterface $logger */
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $this->exceptionListener = new ExceptionListener($logger);
    }

    /**
     * @test
     */
    public function willLogExceptionsWith4xxCodesAsInputErrorNotices()
    {
        for ($i = 0; $i < 99; $i++) {
            $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
            $logger
                ->expects($this->once())
                ->method('notice')
                ->with($this->stringStartsWith('Input error'));

            /** @var LoggerInterface $logger */
            $this->exceptionListener->setLogger($logger);
            $this->codeProperty->setValue($this->exception, 400 + $i);
            $this->exceptionListener->onKernelException($this->event);
        }
    }

    /**
     * @test
     */
    public function willLogExceptionsWith5xxCodesAsRuntimeErrors()
    {
        for ($i = 0; $i < 99; $i++) {
            $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
            $logger
                ->expects($this->once())
                ->method('error')
                ->with($this->stringStartsWith('Runtime error'));

            /** @var LoggerInterface $logger */
            $this->exceptionListener->setLogger($logger);
            $this->codeProperty->setValue($this->exception, 500 + $i);
            $this->exceptionListener->onKernelException($this->event);
        }
    }

    /**
     * @test
     */
    public function willLogExceptionsWithUnexpectedCodesAsCriticalErrors()
    {
        $sample = [4096, 777, 22, 5, 0];
        foreach ($sample as $code) {
            $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
            $logger
                ->expects($this->once())
                ->method('critical')
                ->with($this->stringStartsWith('Runtime error'));

            /** @var LoggerInterface $logger */
            $this->exceptionListener->setLogger($logger);
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
        }
    }

    /**
     * @test
     */
    public function willSetResponseWithVndErrorHeader()
    {
        foreach ([400, 500] as $code) {
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
            $response = $this->event->getResponse();
            $this->assertContains('application/vnd.error', $response->headers->get('Content-Type'));
        }
    }

    /**
     * @test
     */
    public function willSetResponseWithValidJsonContent()
    {
        foreach ([400, 500] as $code) {
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
            $response = $this->event->getResponse();
            $this->assertNotNull(json_decode($response->getContent()));
        }
    }

    /**
     * @test
     */
    public function willSetResponseWithSimpleMessage()
    {
        foreach ([400 => 'Input Error', 500 => 'Server Error'] as $code => $message) {
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
            $response = $this->event->getResponse();
            $this->assertEquals($message, json_decode($response->getContent())->message);
        }
    }

    /**
     * @test
     */
    public function willSetResponseWithLogRef()
    {
        foreach ([400, 500] as $code) {
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
            $response = $this->event->getResponse();
            $this->assertNotNull(json_decode($response->getContent())->logref);
        }
    }

    /**
     * @test
     */
    public function logRefInResponseAndLogMatch()
    {
        foreach ([400, 500] as $code) {
            $logRef = null;
            $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
            $logger
                ->expects($this->once())
                ->method($this->anything())
                ->with($this->callback(function ($message) use (&$logRef) {
                    $matches = [];
                    if (preg_match('/logref ([a-z0-9]*)/', $message, $matches)) {
                        $logRef = $matches[1];

                        return true;
                    }

                    return false;
                }));

            /** @var LoggerInterface $logger */
            $this->exceptionListener->setLogger($logger);
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
            $response = $this->event->getResponse();
            $this->assertEquals($logRef, json_decode($response->getContent())->logref);
        }
    }
}
