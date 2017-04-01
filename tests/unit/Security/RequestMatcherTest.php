<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Routing;

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
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decriptionMock;

    /**
     * @var RequestMatcher
     */
    private $matcher;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        $this->decriptionMock = $this
            ->getMockBuilder(Description::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Repository $repository */
        $this->repositoryMock = $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->decriptionMock);

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
     * @param bool $securedOperation
     * @return Request
     */
    private function createRequest(bool $securedOperation): Request
    {
        $this->repositoryMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->decriptionMock);

        $pathMock = $this
            ->getMockBuilder(Path::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->decriptionMock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($pathMock);

        $pathMock
            ->expects($this->once())
            ->method('getOperation')
            ->willReturn(
                new Operation('', '', '', [], null, [], [], $securedOperation)
            );

        $attributes = [RequestMeta::ATTRIBUTE_URI => 'http://acme.com', RequestMeta::ATTRIBUTE_PATH => '/foo'];

        return new Request([], [], $attributes);
    }
}
