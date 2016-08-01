<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize;

use KleijnWeb\SwaggerBundle\Document\Specification\Operation;
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
     * @expectedException \InvalidArgumentException
     */
    public function unresolvableTypeResultsInException()
    {
        $this->resolver->resolveUsingTypeName('Nope');
    }


    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willThrowExceptionWhenBaseTypeNameCannotBeDeterminedFromSchema()
    {
        $this->resolver->resolveUsingSchema(new \stdClass());
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
    public function canResolveSameTypeTwice()
    {
        $this->assertSame(Stubs\Namespace2\Foo::class, $this->resolver->resolveUsingTypeName('Foo'));
        $this->assertSame(Stubs\Namespace2\Foo::class, $this->resolver->resolveUsingTypeName('Foo'));
    }

    /**
     * @test
     */
    public function canReverseLookupPreviouslyResolvedType()
    {
        $this->resolver->resolveUsingTypeName('Foo');
        $this->assertSame('Foo', $this->resolver->reverseLookup(Stubs\Namespace2\Foo::class));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function reverseLookupWillFailIfNotPreviouslyResolved()
    {
        $this->resolver->reverseLookup(Stubs\Namespace2\Foo::class);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function resolveOperationBodyTypeWillFailWhenOperationHasNoParameters()
    {
        /** @var Operation $operation */
        $operation = $this->getMockBuilder(Operation::class)->disableOriginalConstructor()->getMock();

        $this->resolver->resolveOperationBodyType($operation);
    }
}
