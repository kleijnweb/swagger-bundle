<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Serialize\SerializationTypeResolver;
use KleijnWeb\SwaggerBundle\Serialize\Serializer\SwaggerSerializer;
use KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs\Bar;
use KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs\Foo;
use KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs\Meh;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    private $typeResolverMock;

    /**
     * @var SwaggerSerializer
     */
    private $serializer;

    protected function setUp()
    {
        /** @var SerializationTypeResolver $typeResolver */
        $typeResolver = $this->typeResolverMock = $this->getMockBuilder(SerializationTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeResolverMock->expects($this->any())->method('reverseLookup')->willReturn('Foo');
        $this->typeResolverMock
            ->expects($this->any())
            ->method('resolveUsingSchema')
            ->willReturnCallback(function (\stdClass $schema) {
                return "KleijnWeb\\SwaggerBundle\\Tests\\Serialize\\Serializer\\Stubs\\$schema->class";
            });

        $this->serializer = new SwaggerSerializer($typeResolver, self::getTestSchema());
    }

    /**
     * @test
     *
     * @param Foo    $data
     * @param string $expected
     *
     * @dataProvider  serializationProvider
     */
    public function canSerialize(Foo $data, string $expected)
    {
        $actual = $this->serializer->serialize($data);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     *
     * @dataProvider  deserializationProvider
     *
     * @param string $data
     * @param Foo    $expected
     */
    public function canDeSerialize(string $data, Foo $expected)
    {
        $actual = $this->serializer->deserialize($data, Foo::class);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function serializationProvider(): array
    {
        return [
            [
                new Foo('5', new Bar(6, new Meh(1), new Meh(2), new Meh(3))),
                json_encode([
                    'a'   => '5',
                    'bar' => [
                        'b'    => 6,
                        'meh'  => ['c' => 1],
                        'mehs' => [
                            ['c' => 2],
                            ['c' => 3],
                        ]
                    ]
                ])
            ],
            [
                // Since this will not pass validation, were going to allow it
                new Foo('5', new Bar(6, new Meh([1]), new Meh(2), new Meh(3))),
                json_encode([
                    'a'   => '5',
                    'bar' => [
                        'b'    => 6,
                        'meh'  => ['c' => [1]],
                        'mehs' => [
                            ['c' => 2],
                            ['c' => 3],
                        ]
                    ]
                ])
            ]
        ];
    }

    /**
     * @return array
     */
    public function deserializationProvider(): array
    {
        return [
            [
                json_encode([
                    'a'   => '5',
                    'bar' => [
                        'b'    => 6,
                        'meh'  => ['c' => 1],
                        'mehs' => [
                            ['c' => 2],
                            ['c' => 3],
                        ]
                    ]
                ]),
                new Foo('5', new Bar(6, new Meh(1), new Meh(2), new Meh(3))),
            ],
            [
                json_encode([
                    'a'   => '5',
                    'bar' => [
                        'b'    => 6,
                        'meh'  => ['c' => [1]],
                        'mehs' => [
                            ['c' => 2],
                            ['c' => 3],
                        ]
                    ]
                ]),
                new Foo('5', new Bar(6, new Meh([1]), new Meh(2), new Meh(3))),
            ]
        ];
    }

    /**
     * @return \stdClass
     */
    private static function getTestSchema(): \stdClass
    {
        return (object)[
            'Foo' => (object)[
                'class'      => 'Foo',
                'type'       => 'object',
                'properties' => (object)[
                    'a'   => (object)['type' => 'int'],
                    'bar' => (object)[
                        'class'      => 'Bar',
                        'type'       => 'object',
                        'properties' => (object)[
                            'b'    => (object)['type' => 'int'],
                            'meh'  => (object)[
                                'class'      => 'Meh',
                                'type'       => 'object',
                                'properties' => (object)['a' => (object)['type' => 'int']]
                            ],
                            'mehs' => (object)[
                                'type'  => 'array',
                                'items' => (object)[
                                    'class'      => 'Meh',
                                    'type'       => 'object',
                                    'properties' => (object)['a' => (object)['type' => 'int']]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
