<?php

namespace KleijnWeb\SwaggerBundle\Tests\DependencyInjection;

use KleijnWeb\SwaggerBundle\DependencyInjection\KleijnWebSwaggerExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class KleijnWebSwaggerExtensionTest extends AbstractExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new KleijnWebSwaggerExtension()
        ];
    }

    public function testExceptionListenerRegisteredByDefault()
    {
        $this->load();
        $this->assertContainerBuilderHasService('kernel.listener.swagger.exception');
    }

    public function testExceptionListenerNotRegisteredWhenDisabled()
    {
        $this->load([
            'listeners' => [
                'exception' => false,
            ],
        ]);
        $this->assertContainerBuilderNotHasService('kernel.listener.swagger.exception');
    }
}
