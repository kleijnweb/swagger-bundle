<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\Response\ResponseFactory;
use KleijnWeb\SwaggerBundle\EventListener\ViewListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ViewListenerTest extends TestCase
{
    /**
     * @test
     */
    public function willNotHandleIfNoDocumentUriInAttributes()
    {
        $eventMock = $this
            ->getMockBuilder(GetResponseForControllerResultEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn(new Request());

        $eventMock
            ->expects($this->never())
            ->method('getControllerResult');

        $eventMock
            ->expects($this->never())
            ->method('setResponse');

        $factoryMock = $this
            ->getMockBuilder(ResponseFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock
            ->expects($this->never())
            ->method('createResponse');

        /** @var ResponseFactory $factoryMock */
        $listener = new ViewListener($factoryMock);
        /** @var GetResponseForControllerResultEvent $eventMock */
        $listener->onKernelView($eventMock);
    }

    /**
     * @test
     */
    public function willSetResponseFromFactoryOnEvent()
    {
        $attributes = [RequestMeta::ATTRIBUTE_URI => '/foo/bar'];
        $request = new Request($query = [], $request = [], $attributes);
        $response = new Response();
        $result = [uniqid()];

        $eventMock = $this
            ->getMockBuilder(GetResponseForControllerResultEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $eventMock
            ->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($result);
        $eventMock
            ->expects($this->once())
            ->method('setResponse')
            ->willReturn($response);

        $factoryMock = $this
            ->getMockBuilder(ResponseFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock
            ->expects($this->once())
            ->method('createResponse')
            ->with($request, $result)
            ->willReturn($response);

        /** @var ResponseFactory $factoryMock */
        $listener = new ViewListener($factoryMock);
        /** @var GetResponseForControllerResultEvent $eventMock */
        $listener->onKernelView($eventMock);
    }
}
