<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $logRef = uniqid();
        $exception = $event->getException();

        $code = $exception->getCode();

        if (strlen($code) !== 3) {
            $this->fallback($message, $code, $logRef, $exception);
        } else {
            switch (substr($code, 0, 1)) {
                case '4':
                    $message = 'Input Error';
                    $this->logger->notice("Input error [logref $logRef]: " . $exception->__toString());
                    break;
                case '5':
                    $message = 'Server Error';
                    $this->logger->error("Runtime error [logref $logRef]: " . $exception->__toString());
                    break;
                default:
                    $this->fallback($message, $code, $logRef, $exception);
            }
        }

        $event->setResponse(
            new JsonResponse(
                [
                    "message" => $message,
                    "logref"  => $logRef
                ],
                $code,
                ['Content-Type' => 'application/vnd.error+json']
            )
        );
    }

    /**
     * @param string     $message
     * @param string     $code
     * @param string     $logRef
     * @param \Exception $exception
     */
    private function fallback(&$message, &$code, $logRef, \Exception $exception)
    {
        $code = 500;
        $message = 'Server Error';
        $this->logger->critical("Runtime error [logref $logRef]: " . $exception->__toString());
    }
}
