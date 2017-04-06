<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Security;

use KleijnWeb\SwaggerBundle\Security\RequestAuthorizationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestAuthorizationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var RequestAuthorizationListener
     */
    private $listener;

    protected function setUp()
    {
        $this->authorizationChecker = $this->getMockForAbstractClass(AuthorizationCheckerInterface::class);
        $this->listener = new RequestAuthorizationListener($this->authorizationChecker);
    }

    /**
     * @test
     */
    public function willNotHandleUnlessMasterRequest()
    {
        $this->listener->handle($this->createKernelEventWithRequest(new Request(), false));
    }

    /**
     * @test
     */
    public function willInvokeAuthorizationCheckerWithCorrectAttributeAndRequest()
    {
        $request  = new Request();

        /** @var \PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->authorizationChecker;
        $mock
            ->expects($this->once())
            ->method('isGranted')
            ->with(RequestAuthorizationListener::ATTRIBUTE, $request)
            ->willReturn(true);

        $this->listener->handle($this->createKernelEventWithRequest($request));
    }

    /**
     * @test
     */
    public function willThrowAccessDeniedExceptionWhenAuthorizationCheckerReturnsFalse()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->authorizationChecker;
        $mock
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->listener->handle($this->createKernelEventWithRequest(new Request()));
    }

    private function createKernelEventWithRequest(Request $request, $isMaster = true): GetResponseEvent
    {
        $mock = $this->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $mock
            ->expects($this->any())
            ->method('isMasterRequest')
            ->willReturn($isMaster);

        return $mock;
    }
}
