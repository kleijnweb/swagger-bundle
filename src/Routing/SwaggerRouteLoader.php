<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Routing;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerRouteLoader extends Loader
{
    /**
     * @var array
     */
    private $loadedSpecs = [];

    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @param DocumentRepository $documentRepository
     */
    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param mixed $resource
     * @param null  $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        $resource = (string)$resource;
        if (in_array($resource, $this->loadedSpecs)) {
            throw new \RuntimeException("Resource '$resource' was already loaded");
        }

        $document = $this->documentRepository->get($resource);

        $routes = new RouteCollection();

        foreach ($document->getPathDefinitions() as $path => $methods) {
            $relativePath = ltrim($path, '/');
            $resourceName = strpos($relativePath, '/')
                ? substr($relativePath, 0, strpos($relativePath, '/'))
                : $relativePath;
            foreach ($methods as $methodName => $operationSpec) {
                $operationName = $methodName;
                $controllerKey = "swagger.controller.$resourceName:$operationName";
                if (isset($operationSpec['operationId'])) {
                    $operationName = $operationSpec['operationId'];
                    if (false !== strpos($operationSpec['operationId'], ':')) {
                        $controllerKey = $operationSpec['operationId'];
                    } else {
                        $controllerKey = "swagger.controller.$resourceName:$operationName";
                    }
                }

                $defaults = [
                    '_controller'   => $controllerKey,
                    '_definition'   => $resource,
                    '_swagger_path' => $path
                ];
                $requirements = [];
                $route = new Route($path, $defaults, $requirements);
                $route->setMethods($methodName);
                $routeName = "swagger.{$this->createRouteIdFromPath($path)}.$operationName";
                $routes->add($routeName, $route);
            }
        }

        $this->loadedSpecs[] = $resource;

        return $routes;
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
     * @param string $path
     *
     * @return string
     */
    private function createRouteIdFromPath($path)
    {
        return strtolower(trim(preg_replace('/\W+/', '-', $path), '-'));
    }
}
