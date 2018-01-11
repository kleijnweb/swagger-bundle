<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

use Symfony\Component\BrowserKit\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiTestRequest extends Request
{
    /**
     * @param string $uri
     *
     * @return ApiTestRequest
     */
    public function setUri(string $uri): ApiTestRequest
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return ApiTestRequest
     */
    public function setMethod(string $method): ApiTestRequest
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return ApiTestRequest
     */
    public function setParameters(array $parameters): ApiTestRequest
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param array $files
     *
     * @return ApiTestRequest
     */
    public function setFiles(array $files): ApiTestRequest
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @param array $cookies
     *
     * @return ApiTestRequest
     */
    public function setCookies(array $cookies): ApiTestRequest
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * @param array $server
     *
     * @return ApiTestRequest
     */
    public function setServer(array $server): ApiTestRequest
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @param mixed $content
     *
     * @return ApiTestRequest
     */
    public function setContent($content): ApiTestRequest
    {
        $this->content = $content;

        return $this;
    }
}
