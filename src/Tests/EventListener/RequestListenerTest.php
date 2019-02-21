<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\EventListener\RequestListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_PATH = '/what/a/crock';
    const SWAGGER_PATH  = '/a/b/{hello}';

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
        $this->request = new Request(
            [],
            [],
            ['_definition' => self::DOCUMENT_PATH, '_swagger_path' => self::SWAGGER_PATH]
        );

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
            ->method('getOperationObject')
            ->willReturn(OperationObject::createFromOperationDefinition((object)[]));

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
            ->method('process')
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
            ->method('getOperationObject');

        $this->transformerMock
            ->expects($this->never())
            ->method('process');

        $this->listener->onKernelRequest($this->eventMock);
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

        $this->listener->onKernelRequest($this->eventMock);
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
            ['_definition' => self::DOCUMENT_PATH]
        );

        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->eventMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($wrongRequest);

        $this->listener->onKernelRequest($this->eventMock);
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
            ->method('getOperationObject')
            ->with(self::SWAGGER_PATH)
            ->willReturn(OperationObject::createFromOperationDefinition((object)[]));

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
            ->method('process')
            ->with($this->request);

        $this->listener->onKernelRequest($this->eventMock);
    }

    /**
     * @dataProvider mimeTypeProvider
     * @test
     *
     * @param array $types
     * @param bool  $process
     */
    public function willIgnoreRequestThatExplicitlyDoesNotConsumeJson(array $types, $process)
    {
        $this->eventMock
            ->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->documentMock
            ->expects($this->once())
            ->method('getOperationObject')
            ->with(self::SWAGGER_PATH)
            ->willReturn(
                OperationObject::createFromOperationDefinition((object)[
                    'consumes' => $types,
                ])
            );

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
            ->expects($process ? $this->once() : $this->never())
            ->method('process')
            ->with($this->request);

        $this->listener->onKernelRequest($this->eventMock);
    }

    public static function mimeTypeProvider()
    {
        return [
            [['text/csv'], false],
            [['text/csv', 'image/jpg'], false],
            [['text/json'], true],
            [['application/json'], true],
            [['application/foo.bar+json'], true],
        ];
    }
}
