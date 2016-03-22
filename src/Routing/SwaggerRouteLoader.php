<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Routing;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

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
    public function load($resource, $type = null)
    {
        $resource = (string)$resource;
        if (in_array($resource, $this->loadedSpecs)) {
            throw new \RuntimeException("Resource '$resource' was already loaded");
        }

        $document = $this->documentRepository->get($resource);

        $routes = new RouteCollection();

        $paths = $document->getPathDefinitions();
        $router = 'swagger.controller';
        foreach ($paths as $path => $pathSpec) {
            if ($path === 'x-router') {
                $router = $pathSpec;
                unset($paths->$path);
            }
        }
        foreach ($paths as $path => $methods) {
            $relativePath = ltrim($path, '/');
            $resourceName = strpos($relativePath, '/')
                ? substr($relativePath, 0, strpos($relativePath, '/'))
                : $relativePath;
            $routerController = null;
            foreach ($methods as $methodName => $operationSpec) {
                if ($methodName === 'x-router-controller') {
                    $routerController = $operationSpec;
                    unset($methods->$methodName);
                }
            }
            foreach ($methods as $methodName => $operationSpec) {
                $controllerKey = $this->resolveControllerKey(
                    $operationSpec,
                    $methodName,
                    $resourceName,
                    $router,
                    $routerController
                );
                $defaults = [
                    '_controller'   => $controllerKey,
                    '_definition'   => $resource,
                    '_swagger_path' => $path
                ];

                $route = new Route($path, $defaults, $this->resolveRequirements($document, $path, $methodName));
                $route->setMethods($methodName);
                $routes->add($this->createRouteId($resource, $path, $controllerKey), $route);
            }
        }

        $this->loadedSpecs[] = $resource;

        return $routes;
    }

    /**
     * @param SwaggerDocument $document
     * @param                 $path
     * @param                 $methodName
     *
     * @return array
     */
    private function resolveRequirements(SwaggerDocument $document, $path, $methodName)
    {
        $operationObject = $document->getOperationObject($path, $methodName);

        $requirements = [];

        foreach ($operationObject->getParameters() as $paramDefinition) {
            if ($paramDefinition->in === 'path' && isset($paramDefinition->type)) {
                switch ($paramDefinition->type) {
                    case 'integer':
                        $requirements[$paramDefinition->name] = '\d+';
                        break;
                    case 'string':
                        if (isset($paramDefinition->pattern)) {
                            $requirements[$paramDefinition->name] = $paramDefinition->pattern;
                            break;
                        }
                        if (isset($paramDefinition->enum)) {
                            $requirements[$paramDefinition->name] = '(' .
                                implode('|', $paramDefinition->enum)
                                . ')';
                            break;
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
     * @param        $operationSpec
     * @param        $methodName
     * @param        $resourceName
     * @param string $router
     * @param null   $routerController
     *
     * @return string
     */
    private function resolveControllerKey(
        $operationSpec,
        $methodName,
        $resourceName,
        $router,
        $routerController = null
    ) {
        $operationName = $methodName;
        $diKey = "$router.$resourceName";
        if (isset($operationSpec->operationId)) {
            if (false !== strpos($operationSpec->operationId, ':')) {
                return $operationSpec->operationId;
            }
            $operationName = $operationSpec->operationId;
        }

        if (property_exists($operationSpec, 'x-router-controller')) {
            $diKey = $operationSpec->{'x-router-controller'};
        } elseif ($routerController) {
            $diKey = $routerController;
        }

        if (property_exists($operationSpec, 'x-router-controller-method')) {
            $operationName = $operationSpec->{'x-router-controller-method'};
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
    private function createRouteId($resource, $path, $controllerKey)
    {
        list(, $operationName) = explode(':', $controllerKey);
        $fileName = pathinfo($resource, PATHINFO_FILENAME);
        $normalizedPath = strtolower(trim(preg_replace('/\W+/', '.', $path), '.'));
        $routeName = "swagger.{$fileName}.$normalizedPath.$operationName";

        return $routeName;
    }
}
