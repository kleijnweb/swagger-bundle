<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Routing;

use KleijnWeb\SwaggerBundle\Routing\SwaggerRouteLoader;

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
            ->setMethods(['getPathDefinitions'])
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
     */
    public function loadingMultipleDocumentWillPreventRouteKeyCollisions()
    {
        $pathDefinitions = (object)[
            '/a'     => (object)['get' => (object)[]],
            '/a/b'   => (object)['get' => (object)[], 'post' => (object)[]],
            '/a/b/c' => (object)['put' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

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
            ->expects($this->any())
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
        $pathDefinitions = (object)[
            '/a' => (object)['get' => (object)[], 'post' => (object)[]],
            '/b' => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
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
        $pathDefinitions = (object)[
            '/a'     => (object)['get' => (object)[]],
            '/a/b'   => (object)['get' => (object)[]],
            '/a/b/c' => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(3, $routes);
    }

    /**
     * @test
     */
    public function canUseOperationIdAsControllerKey()
    {
        $expected = 'my.controller.key:methodName';
        $pathDefinitions = (object)[
            '/a' => (object)[
                'get'  => (object)[],
                'post' => (object)['operationId' => $expected]
            ],
            '/b' => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

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
        $pathDefinitions = (object)[
            '/a'       => (object)[
                'get'  => (object)[],
                'post' => (object)['x-router-controller-method' => 'myMethodName']
            ],
            '/b'       => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $actual = $routes->get('swagger.path.a.myMethodName');
        $this->assertNotNull($actual);
    }

    /**
     * @test
     */
    public function canUseXRouterControllerForDiKeyInOperation()
    {
        $diKey = 'my.x_router.controller';
        $expected = "$diKey:post";
        $pathDefinitions = (object)[
            '/a' => (object)[
                'get'  => (object)[],
                'post' => (object)['x-router-controller' => $diKey]
            ],
            '/b' => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

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
        $diKey = 'my.x_router.controller';
        $expected = "$diKey:post";
        $pathDefinitions = (object)[
            '/a' => (object)[
                'x-router-controller' => $diKey,
                'get'                 => (object)[],
                'post'                => (object)[]
            ],
            '/b' => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

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
        $router = 'my.x_router';
        $expected = "$router.a:post";
        $pathDefinitions = (object)[
            'x-router' => $router,
            '/a'       => (object)[
                'get'  => (object)[],
                'post' => (object)[]
            ],
            '/b'       => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

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
        $pathDefinitions = (object)[
            '/a'     => (object)['get' => (object)[]],
            '/a/b'   => (object)['get' => (object)[], 'post' => (object)[]],
            '/a/b/c' => (object)['put' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $this->assertCount(4, $routes);
    }

    /**
     * @test
     */
    public function routeCollectionWillContainPathFromSwaggerDoc()
    {
        $pathDefinitions = (object)[
            '/a'                => (object)['get' => (object)[]],
            '/a/b'              => (object)['get' => (object)[]],
            '/a/b/c'            => (object)['get' => (object)[]],
            '/d/f/g'            => (object)['get' => (object)[]],
            '/1/2/3'            => (object)['get' => (object)[]],
            '/foo/{bar}/{blah}' => (object)['get' => (object)[]],
            '/z'                => (object)['get' => (object)[]],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);

        $definitionPaths = array_keys((array)$pathDefinitions);
        sort($definitionPaths);
        $routePaths = array_map(function ($route) {
            return $route->getPath();
        }, $routes->getIterator()->getArrayCopy());
        sort($routePaths);
        $this->assertSame($definitionPaths, $routePaths);
    }

    /**
     * @test
     */
    public function willAddRequirementsForIntegerPathParams()
    {
        $pathDefinitions = (object)[
            '/a' => (object)[
                'get' => (object)[
                    'parameters' => (object)[
                        (object)['name' => 'foo', 'in' => 'path', 'type' => 'integer']
                    ]
                ]
            ],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

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
        $expected = '\d{2}hello';
        $pathDefinitions = (object)[
            '/a' => (object)[
                'get' => (object)[
                    'parameters' => (object)[
                        (object)['name' => 'aString', 'in' => 'path', 'type' => 'string', 'pattern' => $expected]
                    ]
                ]
            ],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

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
        $enum = ['a', 'b', 'c'];
        $expected = '(a|b|c)';
        $pathDefinitions = (object)[
            '/a' => (object)[
                'get' => (object)[
                    'parameters' => (object)[
                        (object)['name' => 'aString', 'in' => 'path', 'type' => 'string', 'enum' => $enum]
                    ]
                ]
            ],
        ];

        $this->documentMock
            ->expects($this->any())
            ->method('getPathDefinitions')
            ->willReturn($pathDefinitions);

        $routes = $this->loader->load(self::DOCUMENT_PATH);
        $actual = $routes->get('swagger.path.a.get');
        $this->assertNotNull($actual);
        $requirements = $actual->getRequirements();
        $this->assertNotNull($requirements);

        $this->assertSame($expected, $requirements['aString']);
    }
}
