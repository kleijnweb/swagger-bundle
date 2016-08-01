<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs\Bar;
use KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs\Foo;
use KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs\Meh;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ObjectDeserializationTest extends ObjectSerializerTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->typeResolverMock->expects($this->once())->method('reverseLookup')->willReturn('Foo');
        $this->typeResolverMock
            ->expects($this->atLeast(1))
            ->method('resolveUsingSchema')
            ->willReturnCallback(function (\stdClass $schema) {
                return "KleijnWeb\\SwaggerBundle\\Tests\\Serialize\\Serializer\\Stubs\\$schema->class";
            });
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
        $actual = $this->serializer->deserialize($data, Foo::class, $this->specification);
        $this->assertEquals($expected, $actual);
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
            ],
            // Will ignore non-existent properties, should be handled by validation
            [
                json_encode([
                    'a'   => '5',
                    'b'   => '5',
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
            ],
            [
                json_encode([
                    'a'         => '5',
                    'bar' => [
                        'b'    => 6,
                        'meh'  => ['c' => [1]],
                        'mehs' => [
                            ['c' => 2],
                            ['c' => 3],
                        ]
                    ],
                    'aDate'     => (new \DateTime('midnight'))->format('Y-m-d'),
                    'aDateTime' => (new \DateTime())->format(\DateTime::ISO8601),
                ]),
                new Foo(
                    '5',
                    new Bar(6, new Meh([1]), new Meh(2), new Meh(3)),
                    new \DateTime('midnight'),
                    new \DateTime()
                ),
            ],
        ];
    }
}
