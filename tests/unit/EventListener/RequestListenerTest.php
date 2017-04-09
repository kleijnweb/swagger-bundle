<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestProcessor;
use KleijnWeb\SwaggerBundle\EventListener\RequestListener;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_PATH = '/hi.yaml';
    const SWAGGER_PATH  = '/a/b/{hello}';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $processorMock;

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
        $this->eventMock = $this->event = $this
            ->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var RequestProcessor $processor */
        $this->processorMock = $processor = $this
            ->getMockBuilder(RequestProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new RequestListener($processor);
    }

    /**
     * @test
     */
    public function willNotHandleIfNotMasterRequest()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->processorMock
            ->expects($this->never())
            ->method('process');

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @test
     */
    public function willNotHandleIfNoDocumentUriInAttributes()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn(
                new Request()
            );

        $this->processorMock
            ->expects($this->never())
            ->method('process');

        $this->listener->onKernelRequest($this->event);
    }

    /**
     * @test
     */
    public function willInvokeProcessor()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $request = new class extends Request
        {
            /**
             */
            function __construct()
            {
                parent::__construct();
                $this->attributes = new ParameterBag([RequestMeta::ATTRIBUTE_URI => '/uri']);
            }
        };

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->processorMock
            ->expects($this->once())
            ->method('process');

        $this->listener->onKernelRequest($this->event);
    }
}
