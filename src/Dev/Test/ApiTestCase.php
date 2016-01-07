<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\Test;

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
trait ApiTestCase
{
    use AssertsTrait;

    /**
     * @var SchemaManager
     */
    protected static $schemaManager;

    /**
     * @var SwaggerDocument
     */
    protected static $document;

    /**
     * @var ApiTestClient
     */
    protected $client;

    /**
     * PHPUnit cannot add this to code coverage
     *
     * @codeCoverageIgnore
     *
     * @param $swaggerPath
     *
     * @throws \org\bovigo\vfs\vfsStreamException
     */
    public static function initSchemaManager($swaggerPath)
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));

        file_put_contents(
            vfsStream::url('root') . '/swagger.json',
            json_encode(Yaml::parse(file_get_contents($swaggerPath)))
        );

        self::$schemaManager = new SchemaManager(vfsStream::url('root') . '/swagger.json');
        self::$document = new SwaggerDocument($swaggerPath);
    }

    /**
     * Create a client, booting the kernel using SYMFONY_ENV = $this->env
     */
    protected function setUp()
    {
        $this->client = static::createClient(['environment' => $this->env ?: 'test', 'debug' => true]);

        parent::setUp();
    }


    /**
     * @param string $path
     * @param array  $params
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    protected function get($path, array $params = [])
    {
        return $this->sendRequest($path, 'GET', $params);
    }

    /**
     * @param string $path
     * @param array  $params
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    protected function delete($path, array $params = [])
    {
        return $this->sendRequest($path, 'DELETE', $params);
    }

    /**
     * @param string $path
     * @param array  $content
     * @param array  $params
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    protected function patch($path, array $content, array $params = [])
    {
        return $this->sendRequest($path, 'PATCH', $params, $content);
    }

    /**
     * @param string $path
     * @param array  $content
     * @param array  $params
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    protected function post($path, array $content, array $params = [])
    {
        return $this->sendRequest($path, 'POST', $params, $content);
    }

    /**
     * @param string $path
     * @param array  $content
     * @param array  $params
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    protected function put($path, array $content, array $params = [])
    {
        return $this->sendRequest($path, 'PUT', $params, $content);
    }

    /**
     * @param string     $path
     * @param array      $method
     * @param array      $params
     * @param array|null $content
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    protected function sendRequest($path, $method, array $params = [], array $content = null)
    {
        $request = new ApiRequest($this->assembleUri($path, $params), $method);
        $defaults = isset($this->defaultServerVars) ? $this->defaultServerVars : [];
        $request->setServer(array_merge($defaults ?: [], ['CONTENT_TYPE' => 'application/json']));
        if ($content !== null) {
            $request->setContent(json_encode($content));
        }
        $this->client->requestFromRequest($request);

        return $this->getJsonForLastRequest($path, $method);
    }


    /**
     * @param string $path
     * @param array  $params
     *
     * @return string
     */
    private function assembleUri($path, array $params = [])
    {
        $uri = $path;
        if ($params) {
            $uri = $path . '?' . http_build_query($params);
        }

        return $uri;
    }

    /**
     * @param string $fullPath
     * @param string $method
     *
     * @return object|null
     * @throws ApiResponseErrorException
     */
    private function getJsonForLastRequest($fullPath, $method)
    {
        $method = strtolower($method);
        $response = $this->client->getResponse();
        $responseContent = $response->getContent();
        $data = json_decode($responseContent);

        if ($response->getStatusCode() !== 204) {
            if (!function_exists('json_last_error_msg')) {
                function json_last_error_msg()
                {
                    static $ERRORS = [
                        JSON_ERROR_NONE           => 'No error',
                        JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
                        JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
                        JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
                        JSON_ERROR_SYNTAX         => 'Syntax error',
                        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
                    ];

                    $error = json_last_error();

                    return isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error';
                }
            }
            $this->assertSame(
                JSON_ERROR_NONE,
                json_last_error(),
                "Not valid JSON: "
                . json_last_error_msg()
                . "(" . var_export($responseContent, true) . ")"
            );
        }

        if (substr($response->getStatusCode(), 0, 1) != '2') {
            if (!isset($this->validateErrorResponse) || $this->validateErrorResponse) {
                $this->validateResponse($response->getStatusCode(), $response, $method, $fullPath, $data);
            }
            // This throws an exception so that tests can catch it when it is expected
            throw new ApiResponseErrorException($data, $response->getStatusCode());
        }

        $this->validateResponse($response->getStatusCode(), $response, $method, $fullPath, $data);

        return $data;
    }

    /**
     * @param          $code
     * @param Response $response
     * @param string   $method
     * @param string   $fullPath
     * @param mixed    $data
     */
    private function validateResponse($code, $response, $method, $fullPath, $data)
    {
        $request = $this->client->getRequest();
        if (!self::$schemaManager->hasPath(['paths', $request->get('_swagger_path'), $method, 'responses', $code])) {
            throw new \UnexpectedValueException(
                "There is no $code response definition for {$request->get('_swagger_path')}:$method. "
            );
        }
        $headers = [];

        foreach ($response->headers->all() as $key => $values) {
            $headers[str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)))] = $values[0];
        }
        $this->assertResponseHeadersMatch($headers, self::$schemaManager, $fullPath, $method, $code);
        $this->assertResponseBodyMatch($data, self::$schemaManager, $fullPath, $method, $code);
    }
}
