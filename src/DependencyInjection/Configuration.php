<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('swagger');

        $rootNode
            ->children()
            ->booleanNode('validate_responses')->defaultFalse()
            ->end()
            ->scalarNode('ok_status_resolver')->defaultFalse()
            ->end()
            ->arrayNode('hydrator')
            ->children()
            ->arrayNode('namespaces')->isRequired()
            ->beforeNormalization()
            ->ifString()
            ->then(
                function ($v) {
                    return [$v];
                }
            )
            ->end()
            ->prototype('scalar')
            ->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('document')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('cache')->isRequired()->defaultFalse()->end()
            ->scalarNode('base_path')->defaultValue('')->end()
            ->end()
            ->end()
            ->arrayNode('security')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('match_unsecured')->defaultFalse()->end()
            ->end()
            ->end()
            ->arrayNode('listeners')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('exception')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
