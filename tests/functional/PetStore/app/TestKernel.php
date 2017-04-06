<?php declare(strict_types = 1);

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new KleijnWeb\SwaggerBundle\KleijnWebSwaggerBundle(),
            new KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\PetStoreBundle(),
        ];

        if (0 === strpos($this->getEnvironment(), 'secure')) {
            $bundles[] = new \Symfony\Bundle\SecurityBundle\SecurityBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config_'.$this->getEnvironment().'.yml');
    }
}
