<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response\Error;

use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class HttpError
{
    /**
     * @var string
     */
    private $logRef;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $severity;

    /**
     * @var string
     */
    private $message;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * HttpError constructor.
     *
     * @param Request       $request
     * @param \Exception    $exception
     * @param LogRefBuilder $logRefBuilder
     */
    public function __construct(Request $request, \Exception $exception, LogRefBuilder $logRefBuilder)
    {
        $this->request   = $request;
        $this->exception = $exception;
        $this->logRef    = $logRefBuilder->create($request, $exception);

        $code = $exception->getCode();

        if ($exception instanceof InvalidParametersException) {
            $this->severity   = LogLevel::NOTICE;
            $this->statusCode = Response::HTTP_BAD_REQUEST;
            $this->message    = "Validation failed";

            return;
        }

        if ($exception instanceof NotFoundHttpException) {
            $this->statusCode = Response::HTTP_NOT_FOUND;
            $this->severity   = LogLevel::INFO;
        } else {
            if ($exception instanceof MethodNotAllowedHttpException) {
                $this->statusCode = Response::HTTP_METHOD_NOT_ALLOWED;
                $this->severity   = LogLevel::WARNING;
            } elseif ($exception instanceof AuthenticationException) {
                $this->statusCode = Response::HTTP_UNAUTHORIZED;
                $this->severity   = LogLevel::WARNING;
            } else {
                if (strlen((string)$code) !== 3) {
                    $this->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                    $this->severity   = LogLevel::CRITICAL;
                } else {
                    $class = (int)substr((string)$code, 0, 1);
                    switch ($class) {
                        case 4:
                            $this->statusCode = Response::HTTP_BAD_REQUEST;
                            $this->severity   = LogLevel::NOTICE;
                            break;
                        case 5:
                            $this->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                            $this->severity   = LogLevel::ERROR;
                            break;
                        default:
                            $this->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                            $this->severity   = LogLevel::CRITICAL;
                    }
                }
            }
        }
        $this->message = Response::$statusTexts[$this->statusCode];
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getLogRef(): string
    {
        return $this->logRef;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
