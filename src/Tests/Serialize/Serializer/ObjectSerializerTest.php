<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Serialize\Serializer\ObjectSerializer;
use KleijnWeb\SwaggerBundle\Serialize\TypeResolver\SerializerTypeDefinitionMap;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class ObjectSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectSerializer
     */
    protected $serializer;

    /**
     * @var SerializerTypeDefinitionMap
     */
    protected $definitionMap;

    protected function setUp()
    {
        $this->markTestIncomplete();

        parent::setUp();

        /** @var \PHPUnit_Framework_MockObject_MockObject $specificationMock */
        $specificationMock = $this->definitionMap = $this->getMockBuilder(SerializerTypeDefinitionMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = new ObjectSerializer();
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
