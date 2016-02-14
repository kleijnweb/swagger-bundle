<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\DomCrawler\Crawler;

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
     * @param Request $request       A Request instance
     * @param bool    $changeHistory Whether to update the history or not (only used internally for back(), forward(),
     *                               and reload())
     *
     * @return Crawler
     */
    public function requestFromRequest(Request $request, $changeHistory = true)
    {
        return $this->target->requestFromRequest($request, $changeHistory);
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->target->getRequest();
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->target->getResponse();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->target->getContainer();
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    public function getKernel()
    {
        return $this->target->getKernel();
    }

    /**
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     */
    public function getProfile()
    {
        return $this->target->getProfile();
    }

    /**
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     */
    public function enableProfiler()
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
