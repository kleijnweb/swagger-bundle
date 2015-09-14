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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ApiTestCase extends WebTestCase
{
    use AssertsTrait;

    /**
     * @var string
     */
    protected $env = 'test';

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
     * @var array
     */
    protected $defaultServerVars = [];

    /**
     * @var bool
     */
    protected $validateErrorResponse = true;

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
        $this->client = static::createClient(['environment' => $this->env]);

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
        $request->setServer(array_merge($this->defaultServerVars, ['CONTENT_TYPE' => 'application/json']));
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
     * @return object
     * @throws ApiResponseErrorException
     */
    private function getJsonForLastRequest($fullPath, $method)
    {
        $method = strtolower($method);
        $response = $this->client->getResponse();
        $responseContent = $response->getContent();

        if ($response->getStatusCode() === 204 && !$responseContent) {
            return null;
        }

        $basePath = isset(self::$document->getDefinition()->basePath) ? self::$document->getDefinition()->basePath : '';
        $relativePath = !$basePath ? $fullPath : substr($fullPath, strlen($basePath));
        $json = json_decode($responseContent);

        if ($response->getStatusCode() !== 200) {
            if ($this->validateErrorResponse) {
                $this->assertResponseBodyMatch(
                    $json,
                    self::$schemaManager,
                    $fullPath,
                    $method,
                    $response->getStatusCode()
                );
            }
            throw new ApiResponseErrorException($json, $response->getStatusCode());
        }

        if (self::$schemaManager->hasPath(['paths', $relativePath, $method, 'responses', '200'])) {
            $this->assertNotNull($json, "Not valid JSON: $responseContent");
            $headers = [];

            foreach ($response->headers->all() as $key => $values) {
                $headers[str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)))] = $values[0];
            }
            $this->assertResponseHeadersMatch($headers, self::$schemaManager, $fullPath, $method, 200);
            $this->assertResponseBodyMatch($json, self::$schemaManager, $fullPath, $method, 200);

            return $json;
        }

        // If there is no response definition, the API should return 204 No Content.
        // With the current spec the behavior is undefined, this must be fixed.
        throw new \UnexpectedValueException(
            "There is no 200 response definition for $relativePath:$method. For empty responses, use 204."
        );
    }
}
