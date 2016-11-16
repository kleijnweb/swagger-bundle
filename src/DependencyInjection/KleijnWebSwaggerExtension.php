<?php
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
        $container->setParameter('swagger.serializer.namespace', $config['serializer']['namespace']);

        $serializerType = $config['serializer']['type'];
        $container->setAlias('swagger.serializer.target', 'swagger.serializer.' . $serializerType);

        if ($serializerType !== 'array') {
            $resolverDefinition = $container->getDefinition('swagger.request.processor.content_decoder');
            $resolverDefinition->addArgument(new Reference('swagger.serializer.type_resolver'));
        }

        if (!empty($config['document']['cache'])) {
            $resolverDefinition = $container->getDefinition('swagger.document.repository');
            $resolverDefinition->addArgument(new Reference($config['document']['cache']));
        }

        $parameterRefBuilderDefinition = $container->getDefinition('swagger.document.parameter_ref_builder');

        $publicDocsConfig = $config['document']['public'];
        $arguments        = [$publicDocsConfig['base_url'], $publicDocsConfig['scheme'], $publicDocsConfig['host']];
        $parameterRefBuilderDefinition->setArguments($arguments);

        if (!$config['disable_error_listener']) {
            $listenerDefinition = $container->getDefinition('kernel.listener.swagger.vnd_error_exception');
            $listenerDefinition->addTag(
                'kernel.event_listener',
                ['event' => 'kernel.exception', 'method' => 'onKernelException']
            );
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return "swagger";
    }
}
