<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\DependencyInjection;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerRequestAuthorizationFactory implements SecurityFactoryInterface
{
    public function getPosition()
    {
        return 'remember_me';
    }

    public function getKey()
    {
        return 'swagger';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
            ->booleanNode('rbac')->defaultFalse()->end()
            ->end();
    }

    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('security/request_voting.yml');

        if (isset($config['rbac']) && $config['rbac']) {
            $loader->load('security/rbac.yml');
        }

        $listenerId = 'swagger.security.listener.request_authorization';

        return ['swagger.security.provider.noop', $listenerId, null];
    }
}
