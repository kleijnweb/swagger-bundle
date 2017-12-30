<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\ExceptionListener;
use KleijnWeb\SwaggerBundle\EventListener\Response\Error\LogRefBuilderInterface;
use KleijnWeb\SwaggerBundle\EventListener\Response\ErrorResponseFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ExceptionListenerTest extends TestCase
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
     * @var LoggerInterface
     */
    private $logger;

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
            ->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getException', 'getRequest', 'setResponse'])
            ->getMock();

        $this->exception    = new \Exception("Mary had a little lamb");
        $reflection         = new \ReflectionClass($this->exception);
        $codeProperty       = $reflection->getProperty('code');
        $this->codeProperty = $codeProperty;
        $this->codeProperty->setAccessible(true);
        $attributes    = [RequestMeta::ATTRIBUTE_URI => '/foo/bar'];
        $this->request = new Request($query = [], $request = [], $attributes);

        $this->event
            ->expects($this->any())
            ->method('getException')
            ->willReturn($this->exception);

        $this->event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        /** @var ErrorResponseFactoryInterface $errorResponseFactory */
        $errorResponseFactory = $this
            ->getMockBuilder(ErrorResponseFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var LogRefBuilderInterface $logRefBuilder */
        $lofRefBuilderMock = $logRefBuilder = $this->getMockForAbstractClass(LogRefBuilderInterface::class);
        $lofRefBuilderMock->expects($this->any())->method('create')->willReturn((string)rand());

        $this->logger            = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->exceptionListener = new ExceptionListener($errorResponseFactory, $logRefBuilder, $this->logger);
    }

    public function testWillNotHandleIfNoDocumentUriInAttributesAndNotHttpException()
    {
        $event = $this
            ->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getException', 'getRequest', 'setResponse'])
            ->getMock();

        $event
            ->expects($this->any())
            ->method('getException')
            ->willReturn(new \Exception("Mary had a little lamb"))
        ;

        $event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn(new Request())
        ;

        $event
            ->expects($this->never())
            ->method('setResponse')
        ;

        /** @var GetResponseForExceptionEvent $event */
        $this->exceptionListener->onKernelException($event);
    }

    public function testWillHandleIfNoDocumentUriInAttributesButHttpException()
    {
        $event = $this
            ->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getException', 'getRequest', 'setResponse'])
            ->getMock();

        $event
            ->expects($this->any())
            ->method('getException')
            ->willReturn(new NotFoundHttpException())
        ;

        $event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn(new Request())
        ;

        $event
            ->expects($this->once())
            ->method('setResponse')
        ;

        /** @var GetResponseForExceptionEvent $event */
        $this->exceptionListener->onKernelException($event);
    }

    public function testWillLogExceptionsWith4xxCodesAsBadRequestNotices()
    {
        for ($i = 0; $i < 99; $i++) {
            $logger = $this->getMockForAbstractClass(LoggerInterface::class);
            $logger
                ->expects($this->once())
                ->method('log')
                ->with(LogLevel::NOTICE, $this->stringStartsWith('Bad Request'));

            /** @var LoggerInterface $logger */
            $this->setLogger($this->exceptionListener, $logger);
            $this->codeProperty->setValue($this->exception, 400 + $i);
            $this->exceptionListener->onKernelException($this->event);
        }
    }

    public function testWillLogExceptionsWith5xxCodesAsRuntimeErrors()
    {
        for ($i = 0; $i < 99; $i++) {
            $logger = $this->getMockForAbstractClass(LoggerInterface::class);
            $logger
                ->expects($this->once())
                ->method('log')
                ->with(LogLevel::ERROR, $this->stringStartsWith('Internal Server Error'));

            /** @var LoggerInterface $logger */
            $this->setLogger($this->exceptionListener, $logger);
            $this->codeProperty->setValue($this->exception, 500 + $i);
            $this->exceptionListener->onKernelException($this->event);
        }
    }

    public function testWillLogExceptionsWithUnexpectedCodesAsCriticalErrors()
    {
        $sample = [4096, 777, 22, 5, 0];
        foreach ($sample as $code) {
            $logger = $this->getMockForAbstractClass(LoggerInterface::class);
            $logger
                ->expects($this->once())
                ->method('log')
                ->with(LogLevel::CRITICAL, $this->stringStartsWith('Internal Server Error'));

            /** @var LoggerInterface $logger */
            $this->setLogger($this->exceptionListener, $logger);
            $this->codeProperty->setValue($this->exception, $code);
            $this->exceptionListener->onKernelException($this->event);
        }
    }

    /**
     * @param ExceptionListener $exceptionListener
     * @param LoggerInterface   $logger
     */
    private function setLogger(ExceptionListener $exceptionListener, LoggerInterface $logger)
    {
        $reflection = new \ReflectionObject($exceptionListener);
        $property   = $reflection->getProperty('logger');
        $property->setAccessible(true);
        $property->setValue($exceptionListener, $logger);
    }
}
