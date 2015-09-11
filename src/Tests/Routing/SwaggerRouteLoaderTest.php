<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\Tests\Routing;

use KleijnWeb\SwaggerBundle\Routing\SwaggerRouteLoader;
use Symfony\Component\Routing\Route;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerRouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_PATH = '/what/a/crock';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $documentMock;

    /**
     * @var SwaggerRouteLoader
     */
    private $loader;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        $this->documentMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Document\SwaggerDocument')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Document\DocumentRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->documentMock);

        $this->loader = new SwaggerRouteLoader($this->repositoryMock);
    }

    /**
     * @test
     */
    public function supportSwaggerAsRouteTypeOnly()
    {
        $this->assertFalse($this->loader->supports('/a/b/c'));
        $this->assertTrue($this->loader->supports('/a/b/c', 'swagger'));
    }

    /**
     * @test
     */
    public function canLoadMultipleDocuments()
    {
        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn([]);

        $this->loader->load(self::DOCUMENT_PATH);
        $this->loader->load(self::DOCUMENT_PATH . '2');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function cannotTryToLoadSameDocumentMoreThanOnce()
    {
        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn([]);

        $this->loader->load(self::DOCUMENT_PATH);
        $this->loader->load(self::DOCUMENT_PATH);
    }

    /**
     * @test
     */
    public function willReturnRouteCollection()
    {
        $this->documentMock
            ->expects($this->once())
            ->method('getPathDefinitions')
            ->willReturn([]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillContainOneRouteForEveryPathAndMethod()
    {
        $pathDefinitions = [
            '/a' => ['get' => [], 'post' => []],
            '/b' => ['get' => []],
        ];

        $this->documentMock
            ->expects($this->once())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(3, $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillIncludeSeparateRoutesForSubPaths()
    {
        $pathDefinitions = [
            '/a'     => ['get' => []],
            '/a/b'   => ['get' => []],
            '/a/b/c' => ['get' => []],
        ];

        $this->documentMock
            ->expects($this->once())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(3, $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillIncludeSeparateRoutesForSubPathMethodCombinations()
    {
        $pathDefinitions = [
            '/a'     => ['get' => []],
            '/a/b'   => ['get' => [], 'post' => []],
            '/a/b/c' => ['put' => []],
        ];

        $this->documentMock
            ->expects($this->once())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(4, $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillContainPatchFromSwaggerDoc()
    {
        $pathDefinitions = [
            '/a'     => ['get' => []],
            '/a/b'   => ['get' => []],
            '/a/b/c' => ['get' => []],
            '/d/f/g' => ['get' => []],
            '/z'     => ['get' => []],
        ];

        $this->documentMock
            ->expects($this->once())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        foreach (array_keys($pathDefinitions) as $path) {
            /** @var Route $route */
            foreach ($routes as $route) {
                if ($route->getPath() === $path) {
                    break 2;
                }
            }
            $this->fail("No route for path '$path'");
        }
    }
}
