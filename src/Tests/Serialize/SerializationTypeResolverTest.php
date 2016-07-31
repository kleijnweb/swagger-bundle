<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize;

use KleijnWeb\SwaggerBundle\Serialize\SerializationTypeResolver;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SerializationTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializationTypeResolver
     */
    private $resolver;

    protected function setUp()
    {
        $this->resolver = new SerializationTypeResolver([
            'KleijnWeb\SwaggerBundle\Tests\Serialize\Stubs\Namespace2',
            'KleijnWeb\SwaggerBundle\Tests\Serialize\Stubs\Namespace1'
        ]);
    }

    /**
     * @test
     */
    public function canResolveTypesInOrderUsingMultipleNamespaces()
    {
        $this->assertSame(Stubs\Namespace2\Foo::class, $this->resolver->resolveUsingTypeName('Foo'));
    }

    /**
     * @test
     */
    public function canResolveTypesInOrderUsingMultipleNamespaces()
    {
        $this->assertSame(Stubs\Namespace2\Foo::class, $this->resolver->resolveUsingTypeName('Foo'));
    }
}
