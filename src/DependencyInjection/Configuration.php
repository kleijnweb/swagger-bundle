<?php
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
        $rootNode = $treeBuilder->root('swagger');

        $rootNode
            ->children()
                ->arrayNode('serializer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('type')
                            ->values(array('array', 'jms', 'symfony'))
                            ->defaultValue('array')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('namespace')->defaultNull()
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
            ->end()
            ;
        return $treeBuilder;
    }
}
