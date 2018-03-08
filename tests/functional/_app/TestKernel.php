<?php declare(strict_types=1);

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

//@codingStandardsIgnoreStart
class TestKernel extends Kernel
{
//@codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \KleijnWeb\SwaggerBundle\KleijnWebSwaggerBundle(),
            new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\PetStoreBundle(),
        ];

        if (0 === strpos($this->getEnvironment(), 'secure')) {
            $bundles[] = new \Symfony\Bundle\SecurityBundle\SecurityBundle();
        }

        return $bundles;
    }

    public function getCacheDir()
    {
        return __DIR__ . '/var/cache/' . $this->environment;
    }

    public function getLogDir()
    {
        return __DIR__ . '/var/logs/' . $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
