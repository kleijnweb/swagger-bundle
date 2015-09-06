<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\SwaggerBundle\EventListener\ViewListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ViewListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willSetResponseFromFactoryOnEvent()
    {
        $request = new Request();
        $response = new Response();
        $result = [uniqid()];

        $eventMock = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')
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
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Response\ResponseFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock
            ->expects($this->once())
            ->method('createResponse')
            ->with($request, $result)
            ->willReturn($response);

        $listener = new ViewListener($factoryMock);
        $listener->onKernelView($eventMock);
    }
}
