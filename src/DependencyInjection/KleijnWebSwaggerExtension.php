<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class KleijnWebSwaggerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('swagger.document.base_path', $config['document']['base_path']);

        if (isset($config['document']['cache'])) {
            $resolverDefinition = $container->getDefinition('swagger.description.repository');
            $resolverDefinition->addArgument(new Reference($config['document']['cache']));
        }
        if (isset($config['hydrator'])) {
            $container
                ->getDefinition('swagger.hydrator.class_name_resolver')
                ->replaceArgument(0, $config['hydrator']['namespaces']);

            $definition = $container->getDefinition('swagger.request.processor');
            $definition->addArgument(new Reference('swagger.hydrator'));
        }
        if ($config['validate_responses']) {
            $responseFactory = $container
                ->getDefinition('swagger.response.factory');

            if (!isset($config['hydrator'])) {
                $responseFactory->addArgument(null);
            }
            $responseFactory->addArgument(new Reference('swagger.request.validator'));
        }
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return "swagger";
    }
}
