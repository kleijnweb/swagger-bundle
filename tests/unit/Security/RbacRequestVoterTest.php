<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Security;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\Security\RbacRequestVoter;
use KleijnWeb\SwaggerBundle\Security\RequestAuthorizationListener;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RbacRequestVoterTestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var RbacRequestVoter
     */
    private $voter;

    protected function setUp()
    {
        $this->accessDecisionManager = $this->getMockForAbstractClass(AccessDecisionManagerInterface::class);

        /** @var Repository $repository */
        $this->repositoryMock = $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new RbacRequestVoter($this->repositoryMock, $this->accessDecisionManager);
    }

    /**
     * @test
     */
    public function legacySupportsClassMethodReturnsFalse()
    {
        $this->assertFalse($this->voter->supportsClass('Foo'));
    }

    /**
     * @test
     */
    public function willAbstainWhenNotPassedRequest()
    {
        /** @var TokenInterface $token */
        $token = $this->getMockForAbstractClass(TokenInterface::class);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($token, new \stdClass(), []));
    }

    /**
     * @test
     */
    public function willAbstainWhenRequestHasNoSwaggerPath()
    {
        /** @var TokenInterface $token */
        $token = $this->getMockForAbstractClass(TokenInterface::class);

        $this->assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, $this->createRequest([]), [RequestAuthorizationListener::ATTRIBUTE])
        );
    }

    /**
     * @test
     */
    public function willAbstainWhenAttributesNotFromListener()
    {
        /** @var TokenInterface $token */
        $token = $this->getMockForAbstractClass(TokenInterface::class);

        $this->assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, $this->createRequest([]), ['something else'])
        );
    }

    /**
     * @test
     */
    public function willNotAbstainOneAttributesFromListener()
    {
        /** @var TokenInterface $token */
        $token = $this->getMockForAbstractClass(TokenInterface::class);

        $this->repositoryMock->expects($this->once())->method('get');

        $this->voter->vote(
            $token,
            $this->createRequest([
                RequestMeta::ATTRIBUTE_URI  => '/',
                RequestMeta::ATTRIBUTE_PATH => '/',
            ]),
            ['something else', RequestAuthorizationListener::ATTRIBUTE]
        );
    }

    /**
     * @test
     */
    public function willRequireIsAuthenticatedFullyWhenOperationSecured()
    {
        /** @var TokenInterface $token */
        $token = $this->getMockForAbstractClass(TokenInterface::class);

        /** @var Operation $operation */
        $operationMock = $operation = $this->getMockBuilder(Operation::class)->disableOriginalConstructor()->getMock();

        /** @var Description $description */
        $description = $this->getMockBuilder(Description::class)->disableOriginalConstructor()->getMock();

        $request = $this->createRequest([RequestMeta::ATTRIBUTE => new RequestMeta($description, $operation)]);

        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $request, [RequestAuthorizationListener::ATTRIBUTE])
        );

        $operationMock->expects($this->once())->method('isSecured')->willReturn(true);

        /** @var \PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->accessDecisionManager;
        $mock
            ->expects($this->once())
            ->method('decide')
            ->with($token, ['IS_AUTHENTICATED_FULLY'])
            ->willReturn(true);

        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $request, [RequestAuthorizationListener::ATTRIBUTE])
        );
    }

    /**
     * @test
     */
    public function willPassTokenToAccessDecisionManager()
    {
        /** @var TokenInterface $token */
        $token = $this->getMockForAbstractClass(TokenInterface::class);

        $this->voter->vote($token, $this->createRequest([]), []);
    }

    /**
     * @param array  $attributes
     *
     * @param string $content
     *
     * @return Request
     */
    private function createRequest(array $attributes): Request
    {
        return new class($attributes) extends Request
        {
            /**
             * @param array $attributes
             * @param array $content
             */
            public function __construct(array $attributes)
            {
                parent::__construct();
                $this->attributes = new ParameterBag($attributes);
            }
        };
    }
}
