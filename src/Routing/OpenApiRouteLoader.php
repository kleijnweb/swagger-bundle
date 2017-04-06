<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Routing;

use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestMeta;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OpenApiRouteLoader extends Loader
{
    /**
     * @var array
     */
    private $descriptions = [];

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param mixed  $resource
     * @param string $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return 'swagger' === $type;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param mixed $resource
     * @param null  $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null): RouteCollection
    {
        $resource = (string)$resource;
        if (in_array($resource, $this->descriptions)) {
            throw new \RuntimeException("Resource '$resource' was already loaded");
        }

        $description = $this->repository->get($resource);

        $routes           = new RouteCollection();
        $router           = $description->getExtension('router') ?: 'swagger.controller';
        $routerController = $description->getExtension('router-controller');

        foreach ($description->getPaths() as $pathItem) {
            $relativePath = ltrim($pathItem->getPath(), '/');
            $resourceName = strpos($relativePath, '/')
                ? substr($relativePath, 0, strpos($relativePath, '/'))
                : $relativePath;

            $routerController = $pathItem->getExtension('router-controller') ?: $routerController;

            foreach ($pathItem->getOperations() as $operation) {
                $controllerKey = $this->resolveControllerKey(
                    $operation,
                    $resourceName,
                    $router,
                    $routerController
                );
                $defaults      = [
                    '_controller'               => $controllerKey,
                    RequestMeta::ATTRIBUTE_URI  => $resource,
                    RequestMeta::ATTRIBUTE_PATH => $pathItem->getPath(),
                ];

                $route = new Route(
                    $pathItem->getPath(),
                    $defaults,
                    $this->resolveRequirements($operation),
                    [],
                    '',
                    $description->getSchemes()
                );
                $route->setMethods($operation->getMethod());
                $routes->add($this->createRouteId($resource, $pathItem->getPath(), $controllerKey), $route);
            }
        }

        $this->descriptions[] = $resource;

        return $routes;
    }

    /**
     * @param Operation $operation
     *
     * @return array
     */
    private function resolveRequirements(Operation $operation): array
    {
        $requirements = [];

        foreach ($operation->getParameters() as $parameter) {
            if ($parameter->getIn() === Parameter::IN_PATH
                && ($schema = $parameter->getSchema()) instanceof ScalarSchema
            ) {
                switch ($schema->getType()) {
                    case Schema::TYPE_INT:
                        $requirements[$parameter->getName()] = '\d+';
                        break;
                    case Schema::TYPE_STRING:
                        /** @var $schema ScalarSchema $pattern */
                        if ($pattern = $schema->getPattern()) {
                            $requirements[$parameter->getName()] = $pattern;
                        } elseif ($enum = $schema->getEnum()) {
                            $requirements[$parameter->getName()] = '('.implode('|', $enum).')';
                        }
                        break;
                    default:
                        //NOOP
                }
            }
        }

        return $requirements;
    }

    /**
     * @param Operation   $operation
     * @param string      $resourceName
     * @param string      $router
     * @param string|null $routerController
     *
     * @return string
     */
    private function resolveControllerKey(
        Operation $operation,
        string $resourceName,
        string $router,
        string $routerController = null
    ): string {

        $operationName = $operation->getMethod();
        $diKey         = "$router.$resourceName";

        if (0 !== strpos($operation->getId(), '/')) {
            if (false !== strpos($operation->getId(), ':')) {
                return $operation->getId();
            }
            $operationName = $operation->getId();
        }

        if ($controller = $operation->getExtension('router-controller')) {
            $diKey = $controller;
        } elseif ($routerController) {
            $diKey = $routerController;
        }

        if ($controllerMethod = $operation->getExtension('router-controller-method')) {
            $operationName = $controllerMethod;
        }

        return "$diKey:$operationName";
    }

    /**
     * @param string $resource
     * @param string $path
     *
     * @param string $controllerKey
     *
     * @return string
     */
    private function createRouteId(string $resource, string $path, string $controllerKey): string
    {
        list(, $operationName) = explode(':', $controllerKey);
        $fileName       = pathinfo($resource, PATHINFO_FILENAME);
        $normalizedPath = strtolower(trim(preg_replace('/\W+/', '.', $path), '.'));
        $routeName      = "swagger.{$fileName}.$normalizedPath.$operationName";

        return $routeName;
    }
}
