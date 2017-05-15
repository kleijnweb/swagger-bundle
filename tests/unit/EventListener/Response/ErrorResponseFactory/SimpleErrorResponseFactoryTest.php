<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener\Response\ErrorResponseFactory;

use KleijnWeb\SwaggerBundle\EventListener\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\EventListener\Response\Error\LogRefBuilderInterface;
use KleijnWeb\SwaggerBundle\EventListener\Response\ErrorResponseFactory\SimpleErrorResponseFactory;
use KleijnWeb\SwaggerBundle\Exception\ValidationException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SimpleErrorResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    const LOGREF = '123456789';

    /**
     * @var SimpleErrorResponseFactory
     */
    private $factory;

    /**
     * @var LogRefBuilderInterface
     */
    private $logRefBuilder;

    protected function setUp()
    {
        $this->factory       = new SimpleErrorResponseFactory();
        $this->logRefBuilder = $mockObject = $this->getMockForAbstractClass(LogRefBuilderInterface::class);
        $mockObject->expects($this->any())->method('create')->willReturn(self::LOGREF);
    }

    /**
     * @test
     */
    public function willSetResponseWithApplicationJsonHeader()
    {
        $response = $this->factory->create(new HttpError(new Request(), new Exception(), $this->logRefBuilder));

        $this->assertContains('application/json', $response->headers->get('Content-Type'));
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
        $validationErrors = ['Wrong.', 'Wrong.', 'Wrong!'];

        $exception = new ValidationException($validationErrors);
        $request   = new Request();

        $response = $this->factory->create(new HttpError($request, $exception, $this->logRefBuilder));

        $this->assertSame($validationErrors, json_decode($response->getContent())->errors);
    }
}
