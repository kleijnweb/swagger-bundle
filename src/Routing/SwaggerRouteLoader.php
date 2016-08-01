<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Routing;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\Specification;
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
    public function load($resource, $type = null): RouteCollection
    {
        $resource = (string)$resource;
        if (in_array($resource, $this->loadedSpecs)) {
            throw new \RuntimeException("Resource '$resource' was already loaded");
        }

        $document = $this->documentRepository->get($resource);

        $routes = new RouteCollection();

        $paths  = $document->getPaths();
        $router = 'swagger.controller';
        foreach ($paths as $path => $pathSpec) {
            if ($path === 'x-router') {
                $router = $pathSpec;
                unset($paths->$path);
            }
        }
        foreach ($paths as $path => $methods) {
            $relativePath     = ltrim($path, '/');
            $resourceName     = strpos($relativePath, '/')
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
                $defaults      = [
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
     * @param Specification   $document
     * @param                 $path
     * @param                 $methodName
     *
     * @return array
     */
    private function resolveRequirements(Specification $document, $path, $methodName): array
    {
        $operationObject = $document->getOperation($path, $methodName);

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
     * @param \stdClass   $operationDefinition
     * @param string      $methodName
     * @param string      $resourceName
     * @param string      $router
     * @param string|null $routerController
     *
     * @return string
     */
    private function resolveControllerKey(
        \stdClass $operationDefinition,
        string $methodName,
        string $resourceName,
        string $router,
        string $routerController = null
    ): string {

        $operationName = $methodName;
        $diKey         = "$router.$resourceName";
        if (isset($operationDefinition->operationId)) {
            if (false !== strpos($operationDefinition->operationId, ':')) {
                return $operationDefinition->operationId;
            }
            $operationName = $operationDefinition->operationId;
        }

        if (property_exists($operationDefinition, 'x-router-controller')) {
            $diKey = $operationDefinition->{'x-router-controller'};
        } elseif ($routerController) {
            $diKey = $routerController;
        }

        if (property_exists($operationDefinition, 'x-router-controller-method')) {
            $operationName = $operationDefinition->{'x-router-controller-method'};
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
    private function createRouteId($resource, $path, $controllerKey): string
    {
        list(, $operationName) = explode(':', $controllerKey);
        $fileName       = pathinfo($resource, PATHINFO_FILENAME);
        $normalizedPath = strtolower(trim(preg_replace('/\W+/', '.', $path), '.'));
        $routeName      = "swagger.{$fileName}.$normalizedPath.$operationName";

        return $routeName;
    }
}
