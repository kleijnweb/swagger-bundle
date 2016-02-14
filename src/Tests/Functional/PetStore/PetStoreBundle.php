<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class PetStoreBundle extends Bundle
{
    /**
     * @return null
     */
    public function getContainerExtension()
    {
        $this->extension = false;

        return null;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}
