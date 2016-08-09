<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\SwaggerBundle\EventListener\RequestListener;
use KleijnWeb\SwaggerBundle\EventListener\RequestMeta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_PATH = '/what/a/crock';
    const SWAGGER_PATH = '/a/b/{hello}';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $documentMock;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $processor;

    /**
     * @var RequestListener
     */
    private $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventMock;

    /**
     * @var GetResponseForExceptionEvent
     */
    private $event;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        $this->markTestSkipped();

        $this->request = new Request(
            [],
            [],
            [RequestMeta::ATTRIBUTE_URI => self::DOCUMENT_PATH, RequestMeta::ATTRIBUTE_PATH => self::SWAGGER_PATH]
        );

        $this->eventMock = $this->event = $this
            ->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Description $document */
        $this->documentMock = $document = $this
            ->getMockBuilder(Description::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Repository $repository */
        $this->repositoryMock = $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();


        /** @var RequestProcessor $processor */
        $this->processor = $processor = $this
            ->getMockBuilder(RequestProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new RequestListener($repository, $processor);
    }

    /**
     * @test
     */
    public function willTellTransformerToCoerceRequest()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->documentMock
            ->expects($this->once())
            ->method('getOperation')
            ->willReturn(Operation::createFromOperationDefinition((object)[]));

        $this->repositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::DOCUMENT_PATH)
            ->willReturn($this->documentMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->processor
            ->expects($this->once())
            ->method('process')
            ->with($this->request);

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @test
     */
    public function willNotTellTransformerToCoerceRequestWhenNotMasterRequest()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->documentMock
            ->expects($this->never())
            ->method('getOperation');

        $this->processor
            ->expects($this->never())
            ->method('process');

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @test
     */
    public function willIgnoreRequestWithoutDefinition()
    {
        $wrongRequest = new Request();

        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($wrongRequest);

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function willFailOnRequestWithDefinitionButWithoutSwaggerPath()
    {
        $wrongRequest = new Request(
            [],
            [],
            [RequestMeta::ATTRIBUTE_URI => self::DOCUMENT_PATH]
        );

        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($wrongRequest);

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @test
     */
    public function canGetOperationDefinitionUsingSwaggerPath()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->documentMock
            ->expects($this->once())
            ->method('getOperation')
            ->with(self::SWAGGER_PATH)
            ->willReturn(Operation::createFromOperationDefinition((object)[]));

        $this->repositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::DOCUMENT_PATH)
            ->willReturn($this->documentMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->processor
            ->expects($this->once())
            ->method('process')
            ->with($this->request);

        $this->listener->onKernelRequest($this->event);
    }
}
