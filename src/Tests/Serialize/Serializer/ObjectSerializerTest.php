<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Serialize\SerializationTypeResolver;
use KleijnWeb\SwaggerBundle\Serialize\Serializer\ObjectSerializer;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ObjectSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeResolverMock;

    /**
     * @var ObjectSerializer
     */
    protected $serializer;

    /**
     * @var Specification
     */
    protected $specification;

    protected function setUp()
    {
        parent::setUp();

        /** @var SerializationTypeResolver $typeResolver */
        $typeResolver = $this->typeResolverMock = $this->getMockBuilder(SerializationTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject $specificationMock */
        $specificationMock = $this->specification = $this->getMockBuilder(Specification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $specificationMock->expects($this->any())->method('getResourceDefinition')->willReturn(self::getTestSchema());

        $this->serializer = new ObjectSerializer($typeResolver);
    }

    /**
     * @return \stdClass
     */
    protected static function getTestSchema(): \stdClass
    {
        return (object)[
            'class'      => 'Foo',
            'type'       => 'object',
            'properties' => (object)[
                'a'         => (object)['type' => 'int'],
                'aDate'     => (object)['type' => 'string', 'format' => 'date'],
                'aDateTime' => (object)['type' => 'string', 'format' => 'date-time'],
                'bar'       => (object)[
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
        ];
    }
}
