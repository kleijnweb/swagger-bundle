<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

use FR3D\SwaggerAssertions\PhpUnit\AssertsTrait;
use FR3D\SwaggerAssertions\SchemaManager;
use JsonSchema\Validator;
use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 *
 * @property bool   validateErrorResponse
 * @property string env
 * @property array  defaultServerVars
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
     * @param string $swaggerPath
     *
     * @throws \InvalidArgumentException
     * @throws \org\bovigo\vfs\vfsStreamException
     */
    public static function initSchemaManager($swaggerPath)
    {
        $swaggerContents = file_get_contents($swaggerPath);
        $swaggerJson = json_decode(
            preg_match('/\.(yaml|yml)$/', $swaggerPath)
                ? json_encode(Yaml::parse($swaggerContents))
                : $swaggerContents
        );

        $validator = new Validator();
        $validator->check(
            $swaggerJson,
            json_decode(file_get_contents(__DIR__ . '/../../assets/swagger-schema.json'))
        );

        if (!$validator->isValid()) {
            throw new \InvalidArgumentException(
                "Swagger '$swaggerPath' not valid"
            );
        }

        $repository     = new DocumentRepository(dirname($swaggerPath));
        self::$document = $repository->get(basename($swaggerPath));

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));

        $swaggerUri = vfsStream::url('root') . '/swagger.json';
        file_put_contents(
            $swaggerUri,
            json_encode(self::$document->getDefinition())
        );

        // Support for SwaggerAssertions v0.6+
        if (method_exists('FR3D\SwaggerAssertions\SchemaManager', 'fromUri')) {
            self::$schemaManager = SchemaManager::fromUri($swaggerUri);
        } else {
            self::$schemaManager = new SchemaManager($swaggerUri);
        }
    }

    /**
     * Create a client, booting the kernel using SYMFONY_ENV = $this->env
     */
    protected function setUp()
    {
        $this->client = static::createClient(['environment' => $this->getEnv(), 'debug' => true]);

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getDefaultServerVars()
    {
        return isset($this->defaultServerVars) ? $this->defaultServerVars : [];
    }

    /**
     * @return array
     */
    protected function getEnv()
    {
        return isset($this->env) ? $this->env : 'test';
    }

    /**
     * @return bool
     */
    protected function getValidateErrorResponse()
    {
        return isset($this->validateErrorResponse) ? $this->validateErrorResponse : false;
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
     * @param string     $method
     * @param array      $params
     * @param array|null $content
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    protected function sendRequest($path, $method, array $params = [], array $content = null)
    {
        $request = new ApiRequest($this->assembleUri($path, $params), $method);
        $request->setServer(array_merge(['CONTENT_TYPE' => 'application/json'], $this->getDefaultServerVars()));
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
        if (count($params)) {
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
        $method   = strtolower($method);
        $response = $this->client->getResponse();
        $json     = $response->getContent();
        $data     = json_decode($json);

        if ($response->getStatusCode() !== 204) {
            static $errors = [
                JSON_ERROR_NONE           => 'No error',
                JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
                JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
                JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX         => 'Syntax error',
                JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
            ];
            $error            = json_last_error();
            $jsonErrorMessage = isset($errors[$error]) ? $errors[$error] : 'Unknown error';
            $this->assertSame(
                JSON_ERROR_NONE,
                json_last_error(),
                "Not valid JSON: " . $jsonErrorMessage . "(" . var_export($json, true) . ")"
            );
        }

        if (substr($response->getStatusCode(), 0, 1) != '2') {
            if ($this->getValidateErrorResponse()) {
                $this->validateResponse($response->getStatusCode(), $response, $method, $fullPath, $data);
            }
            // This throws an exception so that tests can catch it when it is expected
            throw new ApiResponseErrorException($json, $data, $response->getStatusCode());
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
            $statusClass = (int)substr((string)$code, 0, 1);
            if (in_array($statusClass, [4, 5])) {
                return;
            }
            throw new \UnexpectedValueException(
                "There is no $code response definition for {$request->get('_swagger_path')}:$method. "
            );
        }
        $headers = [];

        foreach ($response->headers->all() as $key => $values) {
            $headers[str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)))] = $values[0];
        }
        try {
            try {
                $this->assertResponseMediaTypeMatch(
                    $response->headers->get('Content-Type'),
                    self::$schemaManager,
                    $fullPath,
                    $method
                );
            } catch (\InvalidArgumentException $e) {
                // Not required, so skip if not found
            }

            $this->assertResponseHeadersMatch($headers, self::$schemaManager, $fullPath, $method, $code);
            $this->assertResponseBodyMatch($data, self::$schemaManager, $fullPath, $method, $code);
        } catch (\UnexpectedValueException $e) {
            $statusClass = (int)(string)$code[0];
            if (in_array($statusClass, [4, 5])) {
                return;
            }
        }
    }

    /**
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     *
     * @return mixed
     */
    public abstract function assertSame($expected, $actual, $message = '');
}
