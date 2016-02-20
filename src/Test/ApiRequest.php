<?php
declare(strict_types = 1);
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
class ApiRequest extends Request
{
    /**
     * @param string $uri
     *
     * @return ApiRequest
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return ApiRequest
     */
    public function setMethod(string $method): ApiRequest
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return ApiRequest
     */
    public function setParameters(array $parameters): ApiRequest
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param array $files
     *
     * @return ApiRequest
     */
    public function setFiles(array $files): ApiRequest
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @param array $cookies
     *
     * @return ApiRequest
     */
    public function setCookies(array $cookies): ApiRequest
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * @param array $server
     *
     * @return ApiRequest
     */
    public function setServer(array $server): ApiRequest
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @param mixed $content
     *
     * @return ApiRequest
     */
    public function setContent($content): ApiRequest
    {
        $this->content = $content;

        return $this;
    }
}
