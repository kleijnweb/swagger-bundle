<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\Request as BrowserRequest;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiTestClient extends Client
{
    /**
     * @var Client
     */
    private $target;

    /**
     * {@inheritdoc}
     */
    public function __construct(Client $client)
    {
        $this->target = $client;
    }

    /**
     * Makes a request from a Request object directly.
     *
     * @param BrowserRequest $request       A Request instance
     * @param bool           $changeHistory Whether to update the history or not (only used internally for back(),
     *                                      forward(), and reload())
     *
     * @return Crawler
     */
    public function requestFromRequest(BrowserRequest $request, $changeHistory = true)
    {
        return $this->target->requestFromRequest($request, $changeHistory);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->target->getRequest();
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->target->getResponse();
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->target->getContainer();
    }

    /**
     * @return KernelInterface
     */
    public function getKernel(): KernelInterface
    {
        return $this->target->getKernel();
    }

    /**
     * @return Profile
     */
    public function getProfile(): Profile
    {
        return $this->target->getProfile();
    }

    /**
     * @return Profile
     */
    public function enableProfiler(): Profile
    {
        return $this->target->getProfile();
    }

    /**
     * @return void
     */
    public function disableReboot()
    {
        $this->target->disableReboot();
    }

    /**
     * @return void
     */
    public function enableReboot()
    {
        $this->target->enableReboot();
    }
}
