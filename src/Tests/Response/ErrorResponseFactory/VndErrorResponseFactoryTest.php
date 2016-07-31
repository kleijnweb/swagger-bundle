<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Response\ErrorResponseFactory;

use JsonSchema\Validator;
use KleijnWeb\SwaggerBundle\Document\ParameterRefBuilder;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\Response\Error\LogRefBuilder;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory\VndError\VndValidationErrorFactory;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory\VndErrorResponseFactory;
use Ramsey\VndError\VndError;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndErrorResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    const LOGREF = '123456789';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $errorFactoryMock;

    /**
     * @var VndErrorResponseFactory
     */
    private $factory;

    /**
     * @var LogRefBuilder
     */
    private $logRefBuilder;

    protected function setUp()
    {
        /** @var VndValidationErrorFactory $errorFactory */
        $this->errorFactoryMock = $errorFactory = $this
            ->getMockBuilder(VndValidationErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new VndErrorResponseFactory($errorFactory);

        $this->logRefBuilder = $mockObject = $this->getMockForAbstractClass(LogRefBuilder::class);
        $mockObject->expects($this->any())->method('create')->willReturn(self::LOGREF);
    }

    /**
     * @test
     */
    public function willSetResponseWithVndErrorHeader()
    {
        $response = $this->factory->create(new HttpError(new Request(), new Exception(), $this->logRefBuilder));

        $this->assertContains('application/vnd.error', $response->headers->get('Content-Type'));
    }

    /**
     * @test
     */
    public function willSetResponseWithValidJsonContent()
    {
        $response = $this->factory->create(new HttpError(new Request(), new Exception(), $this->logRefBuilder));

        $this->assertNotNull(json_decode($response->getContent()));
    }

    /**
     * @test
     */
    public function willSetResponseWithSimpleMessage()
    {
        foreach ([400 => 'Bad Request', 500 => 'Internal Server Error'] as $code => $message) {
            $response = $this->factory->create(
                new HttpError(new Request(), new Exception('Ai caramba!', $code), $this->logRefBuilder)
            );
            $this->assertNotNull($body = json_decode($response->getContent()));
            $this->assertEquals($message, $body->message);
        }
    }

    /**
     * @test
     */
    public function willSetResponseWithLogRef()
    {
        foreach ([400, 500] as $code) {
            $response = $this->factory->create(
                new HttpError(new Request(), new Exception('Ai caramba!', $code), $this->logRefBuilder)
            );
            $this->assertSame(self::LOGREF, json_decode($response->getContent())->logref);
        }
    }

    /**
     * @test
     */
    public function willReturn404ResponsesForNotFoundHttpException()
    {
        $response = $this->factory->create(
            new HttpError(new Request(), new NotFoundHttpException(), $this->logRefBuilder)
        );
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function willCreateValidationErrorResponse()
    {
        $exception = new InvalidParametersException('Oh noes', []);
        $request = new Request();

        $this->errorFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($request, $exception, self::LOGREF)
            ->willReturn(new VndError('Try again'));

        $response = $this->factory->create(
            new HttpError($request, $exception, $this->logRefBuilder)
        );
        $this->assertSame(400, $response->getStatusCode());
    }
}
