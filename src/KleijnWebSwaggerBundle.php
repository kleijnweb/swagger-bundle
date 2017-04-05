<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle;

use KleijnWeb\SwaggerBundle\DependencyInjection\KleijnWebSwaggerExtension;
use KleijnWeb\SwaggerBundle\DependencyInjection\SwaggerRequestAuthorizationFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class KleijnWebSwaggerBundle extends Bundle
{
    /**
     * @return string The Bundle namespace
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasExtension('security')) {
            /** @var SecurityExtension $extension */
            $extension = $container->getExtension('security');
            $extension->addSecurityListenerFactory(new SwaggerRequestAuthorizationFactory());
        }
    }

    /**
     * @return ExtensionInterface
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new KleijnWebSwaggerExtension();
        }

        return $this->extension;
    }
}
