<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\EventListener;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\EventListener\RequestListener;
use KleijnWeb\SwaggerBundle\Request\RequestProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_PATH = '/what/a/crock';

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
    private $transformerMock;

    /**
     * @var RequestListener
     */
    private $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventMock;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        $this->request = new Request([], [], ['_definition' => self::DOCUMENT_PATH]);

        $this->documentMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Document\SwaggerDocument')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Document\DocumentRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transformerMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\RequestProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new RequestListener($this->repositoryMock, $this->transformerMock);
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
            ->method('getOperationDefinition')
            ->willReturn([]);

        $this->repositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::DOCUMENT_PATH)
            ->willReturn($this->documentMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->transformerMock
            ->expects($this->once())
            ->method('coerceRequest')
            ->with($this->request);

        $this->listener->onKernelRequest($this->eventMock);
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
            ->method('getOperationDefinition');

        $this->transformerMock
            ->expects($this->never())
            ->method('coerceRequest');

        $this->listener->onKernelRequest($this->eventMock);
    }
}
