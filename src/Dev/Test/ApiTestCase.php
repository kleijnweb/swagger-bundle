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
     * @var SchemaManager
     */
    protected static $schemaManager;

    /**
     * @var ApiTestClient
     */
    protected $client;

    /**
     * @var bool
     */
    protected $validateErrorResponse = true;

    /**
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
    }

    protected function setUp()
    {
        $this->client = static::createClient();

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
        $method = 'GET';
        $this->client->request($method, $this->assembleUri($path, $params));

        return $this->getJsonForLastRequest($path, $method);
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
        $method = 'POST';
        $request = new ApiRequest($this->assembleUri($path, $params), $method);
        $request->setServer(['CONTENT_TYPE' => 'application/json']);
        $request->setContent(json_encode($content));
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
     * @param string $path
     * @param string $method
     *
     * @return object
     * @throws ApiResponseErrorException
     */
    private function getJsonForLastRequest($path, $method)
    {
        $method = strtolower($method);
        $response = $this->client->getResponse();
        $responseContent = $response->getContent();
        $json = json_decode($responseContent);
        $this->assertNotNull($json, "Not valid JSON: $responseContent");
        $headers = [];
        foreach ($response->headers->all() as $key => $values) {
            $headers[str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)))] = $values[0];
        }
        if ($headers['Content-Type'] == 'application/vnd.error+json') {
            if ($this->validateErrorResponse) {
                $this->assertResponseBodyMatch($json, self::$schemaManager, $path, $method, $response->getStatusCode());
            }
            throw new ApiResponseErrorException($json, $response->getStatusCode());
        }
        $this->assertResponseHeadersMatch($headers, self::$schemaManager, $path, $method, 200);
        $this->assertResponseBodyMatch($json, self::$schemaManager, $path, $method, 200);

        return $json;
    }
}
