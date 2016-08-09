<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Routing;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Path;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\SwaggerBundle\Routing\OpenApiRouteLoader;
use Symfony\Component\Routing\Route;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerRouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    const DOCUMENT_PATH = '/totally/non-existent/path';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decriptionMock;

    /**
     * @var OpenApiRouteLoader
     */
    private $loader;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        $this->decriptionMock = $this
            ->getMockBuilder(Description::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Repository $repository */
        $this->repositoryMock = $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->decriptionMock);

        $this->loader = new OpenApiRouteLoader($repository);
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
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([]);

        $this->loader->load(self::DOCUMENT_PATH);
        $this->loader->load(self::DOCUMENT_PATH . '2');
    }

    /**
     * @test
     */
    public function loadingMultipleDocumentWillPreventRouteKeyCollisions()
    {
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [new Operation('', '/a', 'get')]),
            ]);

        $routes1 = $this->loader->load(self::DOCUMENT_PATH);
        $routes2 = $this->loader->load(self::DOCUMENT_PATH . '2');
        $this->assertSame(count($routes1), count(array_diff_key($routes1->all(), $routes2->all())));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function cannotTryToLoadSameDocumentMoreThanOnce()
    {
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([]);

        $this->loader->load(self::DOCUMENT_PATH);
        $this->loader->load(self::DOCUMENT_PATH);
    }

    /**
     * @test
     */
    public function willReturnRouteCollection()
    {
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillContainOneRouteForEveryPathAndMethod()
    {
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [new Operation(uniqid(), '/a', 'get'), new Operation(uniqid(), '/a', 'post')]),
                new Path('/b', [new Operation(uniqid(), '/b', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(3, $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillIncludeSeparateRoutesForSubPaths()
    {
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [new Operation(uniqid(), '/a', 'get')]),
                new Path('/a/b', [new Operation(uniqid(), '/a/b', 'get')]),
                new Path('/a/b/c', [new Operation(uniqid(), '/a/b/c', 'get')]),
            ]);


        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(3, $routes);
    }

    /**
     * @test
     */
    public function canUseOperationIdAsControllerKey()
    {
        $expected = 'my.controller.key:methodName';

        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [
                    new Operation('/a:get', '/a', 'get'),
                    new Operation($expected, '/a', 'post')
                ]),
                new Path('/b', [new Operation('/b:get', '/b', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('swagger.path.a.methodName');
        $this->assertNotNull($actual);
        $this->assertSame($expected, $actual->getDefault('_controller'));
    }

    /**
     * @test
     */
    public function canUseXRouterMethodToOverrideMethod()
    {
        $extensions = ['router-controller-method' => 'myMethodName'];

        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [
                    new Operation('/a:get', '/a', 'get'),
                    new Operation('/a:post', '/a', 'post', [], null, [], $extensions)
                ]),
                new Path('/b', [new Operation('/b:get', '/b', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('swagger.path.a.myMethodName');
        $this->assertNotNull($actual);
    }

    /**
     * @test
     */
    public function canUseXRouterControllerForDiKeyInOperation()
    {
        $diKey      = 'my.x_router.controller';
        $expected   = "$diKey:post";
        $extensions = ['router-controller' => $diKey];
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [
                    new Operation('/a:get', '/a', 'get'),
                    new Operation('/a:post', '/a', 'post', [], null, [], $extensions)
                ]),
                new Path('/b', [new Operation('/b:get', '/b', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('swagger.path.a.post');
        $this->assertNotNull($actual);
        $this->assertSame($expected, $actual->getDefault('_controller'));
    }

    /**
     * @test
     */
    public function canUseXRouterControllerForDiKeyInPath()
    {
        $diKey    = 'my.x_router.controller';
        $expected = "$diKey:post";
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:post', '/a', 'post')])]);

        $this->decriptionMock
            ->expects($this->atLeast(1))
            ->method('getExtension')
            ->willReturnCallback(function (string $name) use ($diKey) {
                return $name == 'router-controller' ? $diKey : null;
            });

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('swagger.path.a.post');
        $this->assertNotNull($actual);
        $this->assertSame($expected, $actual->getDefault('_controller'));
    }

    /**
     * @test
     */
    public function canUseXRouterForDiKeyInPath()
    {
        $router   = 'my.x_router';
        $expected = "$router.a:post";
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:post', '/a', 'post')])]);

        $this->decriptionMock
            ->expects($this->atLeast(1))
            ->method('getExtension')
            ->willReturnCallback(function (string $name) use ($router) {
                return $name == 'router' ? $router : null;
            });

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('swagger.path.a.post');
        $this->assertNotNull($actual);
        $this->assertSame($expected, $actual->getDefault('_controller'));
    }

    /**
     * @test
     */
    public function routeCollectionWillIncludeSeparateRoutesForSubPathMethodCombinations()
    {
        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([
                new Path('/a', [
                    new Operation('/a:get', '/a', 'get'),
                ]),
                new Path('/a/b', [
                    new Operation('/a/b:get', '/a/b', 'get'),
                    new Operation('/a/b:post', '/a/b', 'post')
                ]),
                new Path('/a/b/c', [new Operation('/a/b/c:get', '/a/b/c', 'get')]),
            ]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(4, $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillContainPathFromDescription()
    {
        $paths = [
            new Path('/a', [new Operation('/a:get', '/a', 'get'),]),
            new Path('/a/b', [new Operation('/a/b:get', '/a/b', 'get'),]),
            new Path('/a/b/c', [new Operation('/a/b/c:get', '/a/b/c', 'get')]),
            new Path('/d/f/g', [new Operation('/d/f/g:get', '/d/f/g', 'get')]),
            new Path('/1/2/3', [new Operation('/1/2/3:get', '/1/2/3', 'get')]),
            new Path('/foo/{bar}/{blah}', [new Operation('/foo/{bar}/{blah}:get', '/foo/{bar}/{blah}', 'get')]),
            new Path('/z', [new Operation('/z:get', '/z', 'get'),]),
        ];

        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn($paths);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $descriptionPaths = array_map(function (Path $path) {
            return $path->getPath();
        }, $paths);
        sort($descriptionPaths);

        $routePaths = array_map(function (Route $route) {
            return $route->getPath();
        }, $routes->getIterator()->getArrayCopy());

        sort($routePaths);
        $this->assertSame($descriptionPaths, $routePaths);
    }

    /**
     * @test
     */
    public function willAddRequirementsForIntegerPathParams()
    {
        $parameter = new Parameter(
            'foo',
            true,
            new ScalarSchema((object)['type' => Schema::TYPE_INT]),
            Parameter::IN_PATH
        );

        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:get', '/a', 'get', [$parameter])])]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $actual = $routes->get('swagger.path.a.get');
        $this->assertNotNull($actual);
        $requirements = $actual->getRequirements();
        $this->assertNotNull($requirements);

        $this->assertSame($requirements['foo'], '\d+');
    }

    /**
     * @test
     */
    public function willAddRequirementsForStringPatternParams()
    {
        $expected        = '\d{2}hello';
        $parameter = new Parameter(
            'aString',
            true,
            new ScalarSchema((object)[
                'type' => Schema::TYPE_STRING,
                'pattern' => $expected
            ]),
            Parameter::IN_PATH
        );

        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:get', '/a', 'get', [$parameter])])]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $actual = $routes->get('swagger.path.a.get');
        $this->assertNotNull($actual);
        $requirements = $actual->getRequirements();
        $this->assertNotNull($requirements);

        $this->assertSame($expected, $requirements['aString']);
    }

    /**
     * @test
     */
    public function willAddRequirementsForStringEnumParams()
    {
        $enum            = ['a', 'b', 'c'];
        $expected        = '(a|b|c)';
        $parameter = new Parameter(
            'aString',
            true,
            new ScalarSchema((object)[
                'type' => Schema::TYPE_STRING,
                'enum' => $enum
            ]),
            Parameter::IN_PATH
        );

        $this->decriptionMock
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn([new Path('/a', [new Operation('/a:get', '/a', 'get', [$parameter])])]);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $actual = $routes->get('swagger.path.a.get');
        $this->assertNotNull($actual);
        $requirements = $actual->getRequirements();
        $this->assertNotNull($requirements);

        $this->assertSame($expected, $requirements['aString']);
    }
}
