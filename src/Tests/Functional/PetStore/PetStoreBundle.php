<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class PetStoreBundle extends Bundle
{
    /**
     * @return ExtensionInterface
     */
    public function getContainerExtension()
    {
        return $this->extension = false;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * Gets the Bundle directory path.
     *
     * @return string The Bundle absolute path
     *
     * @api
     */
    public function getPath()
    {
        if (!$this->path) {
            vfsStreamWrapper::register();
            vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));

            $this->path = vfsStream::url('root/PetStoreBundle');

            if (!is_dir($this->path)) {
                mkdir($this->path);
            }
        }

        return $this->path;
    }
}
