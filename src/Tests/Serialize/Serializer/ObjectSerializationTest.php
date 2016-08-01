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
class ObjectSerializationTest extends ObjectSerializerTest
{
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
        $actual = $this->serializer->serialize($data, $this->definitionMap);
        $this->assertSame($expected, $actual);
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
            ],
            [
                new Foo(
                    '5',
                    new Bar(6, new Meh([1]), new Meh(2), new Meh(3)),
                    new \DateTime('midnight'),
                    new \DateTime()
                ),
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
            ],
        ];
    }
}
