<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener\Response\Error;

use KleijnWeb\SwaggerBundle\EventListener\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\EventListener\Response\Error\LogRefBuilderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class HttpErrorTest extends TestCase
{
    /**
     * @var LogRefBuilderInterface
     */
    private $logRefBuilder;

    protected function setUp()
    {
        $this->logRefBuilder = $this->getMockForAbstractClass(LogRefBuilderInterface::class);
    }

    /**
     * @test
     */
    public function willClassifyExceptionsWith4xxCodesAsBadRequestNotices()
    {
        for ($i = 0; $i < 99; $i++) {
            $error = new HttpError(new Request(), new \Exception('mimimimimimi', 400 + $i), $this->logRefBuilder);
            $this->assertSame($error->getSeverity(), LogLevel::NOTICE);
            $this->assertSame($error->getStatusCode(), 400);
            $this->assertStringStartsWith('Bad Request', $error->getMessage());
        }
    }

    /**
     * @test
     */
    public function willClassifyExceptionsWith5xxCodesAsRuntimeErrors()
    {
        for ($i = 0; $i < 99; $i++) {
            $error = new HttpError(new Request(), new \Exception('mimimimimimi', 500 + $i), $this->logRefBuilder);
            $this->assertSame($error->getSeverity(), LogLevel::ERROR);
            $this->assertSame($error->getStatusCode(), 500);
            $this->assertStringStartsWith('Internal Server Error', $error->getMessage());
        }
    }

    /**
     * @test
     */
    public function willClassifyExceptionsWithUnexpectedCodesAsCriticalErrors()
    {
        $sample = [4096, 777, 22, 5, 0];
        foreach ($sample as $code) {
            $error = new HttpError(new Request(), new \Exception('mimimimimimi', $code), $this->logRefBuilder);
            $this->assertSame($error->getSeverity(), LogLevel::CRITICAL);
            $this->assertSame($error->getStatusCode(), 500);
            $this->assertStringStartsWith('Internal Server Error', $error->getMessage());
        }
    }

    /**
     * @test
     */
    public function willClassifyMethodNotAllowedHttpExceptionAsWarningsAndReturn405Status()
    {
        $error = new HttpError(new Request(), new MethodNotAllowedHttpException(['GET']), $this->logRefBuilder);
        $this->assertSame($error->getSeverity(), LogLevel::WARNING);
        $this->assertSame($error->getStatusCode(), 405);
        $this->assertStringStartsWith('Method Not Allowed', $error->getMessage());
    }

    /**
     * @test
     */
    public function willClassifyAuthenticationExceptionAsWarningsAndReturn401Status()
    {
        $error = new HttpError(new Request(), new AuthenticationException(), $this->logRefBuilder);
        $this->assertSame($error->getSeverity(), LogLevel::WARNING);
        $this->assertSame($error->getStatusCode(), 401);
        $this->assertStringStartsWith('Unauthorized', $error->getMessage());
    }
}
