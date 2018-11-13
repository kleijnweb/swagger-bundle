<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Response\Error;

use KleijnWeb\SwaggerBundle\Exception\ValidationException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
     * @var \Exception
     */
    private $exception;

    const HTTP_CODE_SEVERITY = [
        Response::HTTP_UNAUTHORIZED       => LogLevel::WARNING,
        Response::HTTP_FORBIDDEN          => LogLevel::WARNING,
        Response::HTTP_NOT_FOUND          => LogLevel::INFO,
        Response::HTTP_METHOD_NOT_ALLOWED => LogLevel::WARNING,
    ];

    /**
     * HttpError constructor.
     *
     * @param Request                $request
     * @param \Exception             $exception
     * @param LogRefBuilderInterface $logRefBuilder
     */
    public function __construct(Request $request, \Exception $exception, LogRefBuilderInterface $logRefBuilder)
    {
        $this->exception = $exception;
        $this->logRef    = $logRefBuilder->create($request, $exception);

        $code = $exception->getCode();

        if ($exception instanceof ValidationException) {
            $this->severity   = LogLevel::NOTICE;
            $this->statusCode = Response::HTTP_BAD_REQUEST;
            $this->message    = $exception->getMessage();

            return;
        }

        if ($exception instanceof HttpException) {
            $this->statusCode = $exception->getStatusCode();
        } else if ($exception instanceof AuthenticationException) {
            $this->statusCode = Response::HTTP_UNAUTHORIZED;
        } elseif ($exception instanceof AccessDeniedException) {
            $this->statusCode = Response::HTTP_FORBIDDEN;
        }

        if ($this->statusCode && isset(self::HTTP_CODE_SEVERITY[$this->statusCode])) {
            $this->severity = self::HTTP_CODE_SEVERITY[$this->statusCode];
        }

        if (!$this->severity) {
            if (strlen((string)$code) !== 3) {
                $guessedStatusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $this->severity    = LogLevel::CRITICAL;
            } else {
                $class = (int)substr((string)$code, 0, 1);
                switch ($class) {
                    case 4:
                        $guessedStatusCode = Response::HTTP_BAD_REQUEST;
                        $this->severity    = LogLevel::NOTICE;
                        break;
                    case 5:
                        $guessedStatusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                        $this->severity    = LogLevel::ERROR;
                        break;
                    default:
                        $guessedStatusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                        $this->severity    = LogLevel::CRITICAL;
                }
            }
            if (!$this->statusCode) {
                $this->statusCode = $guessedStatusCode;
            }
        }
        $this->message = Response::$statusTexts[$this->statusCode];
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
