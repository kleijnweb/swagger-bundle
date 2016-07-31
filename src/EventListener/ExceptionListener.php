<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\SwaggerBundle\Response\Error\HttpError;
use KleijnWeb\SwaggerBundle\Response\Error\LogRefBuilder;
use KleijnWeb\SwaggerBundle\Response\ErrorResponseFactory;
use Psr\Log\LoggerInterface;
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
     * @var ErrorResponseFactory
     */
    private $errorResponseFactory;

    /**
     * @var LogRefBuilder
     */
    private $logRefBuilder;

    /**
     * @param ErrorResponseFactory $errorResponseFactory
     * @param LogRefBuilder        $logRefBuilder
     * @param LoggerInterface      $logger
     */
    public function __construct(
        ErrorResponseFactory $errorResponseFactory,
        LogRefBuilder $logRefBuilder,
        LoggerInterface $logger
    ) {
        $this->logger               = $logger;
        $this->errorResponseFactory = $errorResponseFactory;
        $this->logRefBuilder        = $logRefBuilder;
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
        $error = new HttpError($event->getRequest(), $event->getException(), $this->logRefBuilder);

        $this->logger->log(
            $error->getSeverity(),
            "{$error->getMessage()} [logref {$error->getLogRef()}]: {$event->getException()}"
        );

        $event->setResponse($this->errorResponseFactory->create($error));
    }
}
