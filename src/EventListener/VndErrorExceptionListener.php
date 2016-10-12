<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Response\VndValidationErrorFactory;
use KleijnWeb\SwaggerBundle\Response\VndErrorResponse;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Ramsey\VndError\VndError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndErrorExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var VndValidationErrorFactory
     */
    private $validationErrorFactory;

    /**
     * @param VndValidationErrorFactory $errorFactory
     * @param LoggerInterface           $logger
     */
    public function __construct(VndValidationErrorFactory $errorFactory, LoggerInterface $logger)
    {
        $this->logger                 = $logger;
        $this->validationErrorFactory = $errorFactory;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @throws \Exception
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $logRef = uniqid();

        try {
            $exception = $event->getException();
            $request   = $event->getRequest();
            $code      = $exception->getCode();

            if ($exception instanceof InvalidParametersException) {
                $severity   = LogLevel::NOTICE;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $vndError   = $this->validationErrorFactory->create($request, $exception, $logRef);
            } else {
                switch(true) {
                    case $exception instanceof NotFoundHttpException:
                        $statusCode = Response::HTTP_NOT_FOUND;
                        $severity   = LogLevel::INFO;
                        break;
                    case $exception instanceof MethodNotAllowedHttpException:
                        $statusCode = Response::HTTP_METHOD_NOT_ALLOWED;
                        $severity   = LogLevel::WARNING;
                        break;
                    case $exception instanceof AuthenticationException:
                        $statusCode = Response::HTTP_UNAUTHORIZED;
                        $severity   = LogLevel::WARNING;
                        break;
                    case $exception instanceof AccessDeniedHttpException:
                        $statusCode = Response::HTTP_FORBIDDEN;
                        $severity   = LogLevel::WARNING;
                        break;
                    default:
                        $is3Digits = strlen($code) === 3;
                        $class     = (int)substr($code, 0, 1);
                        if (!$is3Digits) {
                            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                            $severity   = LogLevel::CRITICAL;
                        } else {
                            switch ($class) {
                                case 4:
                                    $severity   = LogLevel::NOTICE;
                                    $statusCode = Response::HTTP_BAD_REQUEST;
                                    break;
                                case 5:
                                    $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                                    $severity   = LogLevel::ERROR;
                                    break;
                                default:
                                    $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                                    $severity   = LogLevel::CRITICAL;
                            }
                        }
                }

                $message  = Response::$statusTexts[$statusCode];
                $vndError = new VndError($message, $logRef);
                $vndError->addLink('help', $request->get('_definition'), ['title' => 'Error Information']);
                $vndError->addLink('about', $request->getUri(), ['title' => 'Error Information']);
            }

            $reference = $logRef ? " [logref $logRef]" : '';
            $event->setResponse(new VndErrorResponse($vndError, $statusCode));
            $this->logger->log($severity, "{$vndError->getMessage()}{$reference}: $exception");
        } catch (\PHPUnit_Framework_Exception  $e) {
            throw $e;
        } catch (\PHPUnit_Framework_Error  $e) {
            throw $e;
        } catch (\Exception $e) {
            // A simpler response where less can go wrong
            $message  = "Error Handling Failure";
            $vndError = new VndError($message, $logRef);
            $this->logger->log(LogLevel::CRITICAL, "$message [logref $logRef]: $e");
            $event->setResponse(new VndErrorResponse($vndError, Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
