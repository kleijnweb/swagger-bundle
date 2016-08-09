<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\SwaggerBundle\EventListener\RequestMeta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 *
 * @property string env
 * @property array  defaultServerVars
 */
trait ApiTestCase
{
    /**
     * @var ApiTestClient
     */
    protected $client;

    /**
     * @var SchemaValidator
     */
    private $validator;

    /**
     */
    protected function setUp()
    {
        $this->createApiTestClient();
    }

    /**
     * Create a client, booting the kernel using SYMFONY_ENV = $this->env
     */
    protected function createApiTestClient()
    {
        return $this->client = new ApiTestClient(
            static::createClient([
                'environment' => $this->getEnv(),
                'debug'       => true
            ])
        );
    }

    /**
     * @return array
     */
    protected function getDefaultServerVars(): array
    {
        return isset($this->defaultServerVars) ? $this->defaultServerVars : [];
    }

    /**
     * @return string
     */
    protected function getEnv(): string
    {
        return isset($this->env) ? $this->env : 'test';
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     * @throws ApiResponseErrorException
     */
    protected function get(string $path, array $params = [])
    {
        return $this->request($path, 'GET', $params);
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     * @throws ApiResponseErrorException
     */
    protected function delete(string $path, array $params = [])
    {
        return $this->request($path, 'DELETE', $params);
    }

    /**
     * @param string $path
     * @param array  $content
     * @param array  $params
     *
     * @return mixed
     * @throws ApiResponseErrorException
     */
    protected function patch(string $path, array $content, array $params = [])
    {
        return $this->request($path, 'PATCH', $params, $content);
    }

    /**
     * @param string $path
     * @param array  $content
     * @param array  $params
     *
     * @return mixed
     * @throws ApiResponseErrorException
     */
    protected function post(string $path, array $content, array $params = [])
    {
        return $this->request($path, 'POST', $params, $content);
    }

    /**
     * @param string $path
     * @param array  $content
     * @param array  $params
     *
     * @return mixed
     * @throws ApiResponseErrorException
     */
    protected function put(string $path, array $content, array $params = [])
    {
        return $this->request($path, 'PUT', $params, $content);
    }

    /**
     * @param string     $path
     * @param string     $method
     * @param array      $params
     * @param array|null $content
     *
     * @return mixed
     * @throws ApiResponseErrorException
     */
    protected function request(string $path, string $method, array $params = [], array $content = null)
    {
        $apiRequest = new ApiRequest($this->assembleUri($path, $params), $method);
        $apiRequest->setServer(array_merge(['CONTENT_TYPE' => 'application/json'], $this->getDefaultServerVars()));

        if ($content !== null) {
            $apiRequest->setContent(json_encode($content));
        }

        $this->client->requestFromRequest($apiRequest);

        /** @var Response $response */
        $response = $this->client->getResponse();
        /** @var Request $request */
        $request = $this->client->getRequest();

        $body    = null;
        $content = null;

        if ($content = $response->getContent() && $response->getStatusCode() !== Response::HTTP_NO_CONTENT) {
            $body = json_decode($content);
            $this->assertSame(
                JSON_ERROR_NONE,
                json_last_error(),
                "Not valid JSON: " . json_last_error_msg() . "(" . var_export($content, true) . ")"
            );
        }

        if (substr((string)$response->getStatusCode(), 0, 1) != '2') {
            // This throws an exception so that tests can catch it when it is expected
            throw new ApiResponseErrorException($content, $body, $response->getStatusCode());
        }

        /** @var Operation $operation */
        $operation = $request->attributes->get(RequestMeta::ATTRIBUTE)->getOperation();
        $schema    = $operation->getResponse($response->getStatusCode())->getSchema();

        $result = $this->validator->validate($schema, $body);

        if (!$result->isvalid()) {
            throw new \UnexpectedValueException("Invalid response: " . implode(', ', $result->getErrorMessages()));
        }

        return $body;
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return string
     */
    private function assembleUri(string $path, array $params = [])
    {
        $uri = $path;
        if (count($params)) {
            $uri = $path . '?' . http_build_query($params);
        }

        return $uri;
    }

    /**
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     *
     * @return mixed
     */
    abstract public function assertSame($expected, $actual, $message = '');
}
