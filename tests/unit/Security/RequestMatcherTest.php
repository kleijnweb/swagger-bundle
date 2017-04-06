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
use KleijnWeb\PhpApi\Descriptions\Description\Path;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestMeta;
use KleijnWeb\SwaggerBundle\Security\RequestMatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestMatcherTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_PATH = '/totally/non-existent/path';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryStub;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decriptionStub;

    /**
     * @var RequestMatcher
     */
    private $matcher;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        $this->decriptionStub = $this
            ->getMockBuilder(Description::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Repository $repository */
        $this->repositoryStub = $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryStub
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->decriptionStub);

        $this->matcher = new RequestMatcher($repository);
    }

    /**
     * @test
     */
    public function willReturnFalseIfNoDocumentUriInAttributes()
    {
        $this->assertFalse($this->matcher->matches(new Request()));
    }

    /**
     * @test
     */
    public function willReturnFalseWhenOperationNotSecured()
    {
        $this->assertFalse($this->matcher->matches($this->createRequest(false)));
    }

    /**
     * @test
     */
    public function willReturnTrueWhenOperationNotSecured()
    {
        $this->assertTrue($this->matcher->matches($this->createRequest(true)));
    }

    /**
     * @test
     */
    public function settingMatchUnsecuredToTrueWillReturnTrueEvenIfMatchHasNoSecurityInfo()
    {
        $this->matcher->setMatchUnsecured();
        $this->assertTrue($this->matcher->matches($this->createRequest(false)));
    }

    /**
     * @param bool $securedOperation
     * @return Request
     */
    private function createRequest(bool $securedOperation): Request
    {
        $this->repositoryStub
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->decriptionStub);

        $pathStub = $this
            ->getMockBuilder(Path::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->decriptionStub
            ->expects($this->any())
            ->method('getPath')
            ->willReturn($pathStub);

        $pathStub
            ->expects($this->any())
            ->method('getOperation')
            ->willReturn(
                new Operation('', '', '', [], null, [], [], $securedOperation)
            );

        $attributes = [RequestMeta::ATTRIBUTE_URI => 'http://acme.com', RequestMeta::ATTRIBUTE_PATH => '/foo'];

        return new Request([], [], $attributes);
    }
}
