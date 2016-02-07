<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\SwaggerBundle\EventListener\ExceptionListener;
use KleijnWeb\SwaggerBundle\Response\VndValidationErrorFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @var Request
     */
    private $request;

    /**
     * @var ExceptionListener
     */
    private $exceptionListener;

    /**
     * Set up mocking
     */
    protected function setUp()
    {
        $this->event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getException', 'getRequest'])
            ->getMock();

        $this->exception = new \Exception("Mary had a little lamb");
        $reflection = new \ReflectionClass($this->exception);
        $codeProperty = $reflection->getProperty('code');
        $this->codeProperty = $codeProperty;
        $this->codeProperty->setAccessible(true);
        $attributes = [
            '_resource' => '/foo/bar'
        ];
        $this->request = new Request($query = [], $request = [], $attributes);

        $this->event
            ->expects($this->any())
            ->method('getException')
            ->willReturn($this->exception);

        $this->event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        /** @var LoggerInterface $logger */
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $this->exceptionListener = new ExceptionListener(new VndValidationErrorFactory(), $logger);
    }

    /**
     * @test
     */
    public function willLogExceptionsWith4xxCodesAsBadRequestNotices()
    {
        for ($i = 0; $i < 99; $i++) {
            $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
            $logger
                ->expects($this->once())
                ->method('log')
                ->with(LogLevel::NOTICE, $this->stringStartsWith('Bad Request'));

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
                ->method('log')
                ->with(LogLevel::ERROR, $this->stringStartsWith('Internal Server Error'));

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
                ->method('log')
                ->with(LogLevel::CRITICAL, $this->stringStartsWith('Internal Server Error'));

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
        foreach ([400 => 'Bad Request', 500 => 'Internal Server Error'] as $code => $message) {
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
    public function logrefInResponseAndLogMatch()
    {
        foreach ([400, 500] as $code) {
            $logref = null;
            $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
            $logger
                ->expects($this->once())
                ->method('log')
                ->with($this->anything(), $this->callback(function ($message) use (&$logref) {
                    $matches = [];
                    if (preg_match('/logref ([a-z0-9]*)/', $message, $matches)) {
                        $logref = $matches[1];

                        return true;
                    }

                    return false;
                }));

            /** @var LoggerInterface $logger */
            $this->exceptionListener->setLogger($logger);
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
            $response = $this->event->getResponse();
            $this->assertEquals($logref, json_decode($response->getContent())->logref);
        }
    }

    /**
     * @test
     */
    public function willReturn404Responses()
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getException', 'getRequest'])
            ->getMock();

        $event->expects($this->any())
            ->method('getException')
            ->willReturn(new NotFoundHttpException());

        $event->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->exceptionListener->onKernelException($event);
        $response = $event->getResponse();
        $this->assertSame(404, $response->getStatusCode());
    }
}
