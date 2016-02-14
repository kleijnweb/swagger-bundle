<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Response;

use JsonSchema\Validator;
use KleijnWeb\SwaggerBundle\Document\ParameterRefBuilder;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Response\VndValidationErrorFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndValidationErrorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VndValidationErrorFactory
     */
    private $factory;

    /**
     * @var ParameterRefBuilder
     */
    private $refBuilder;

    protected function setUp()
    {
        $this->refBuilder = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Document\ParameterRefBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = new VndValidationErrorFactory($this->refBuilder);
    }

    /**
     * @test
     */
    public function createdErrorCanHaveLogRef()
    {
        $vndError = $this->factory->create(
            $this->createSimpleRequest(),
            new InvalidParametersException('Yikes', []),
            123456789
        );
        $this->assertInstanceOf('Ramsey\VndError\VndError', $vndError);
        $this->assertSame(123456789, $vndError->getLogref());
    }

    /**
     * @test
     */
    public function createdErrorCanHaveNoLogRef()
    {
        $this->assertNull(
            $this->factory->create(
                $this->createSimpleRequest(),
                new InvalidParametersException('Yikes', [])
            )->getLogref()
        );
    }

    /**
     * @test
     */
    public function resultIncludesErrorMessagesCreatedByJsonSchema()
    {
        $value = (object)[
            'foo' => (object)[
                'blah' => 'one'
            ],
            'bar' => (object)[]
        ];
        $validator = new Validator();
        $schema = (object)[
            'type'       => 'object',
            'required'   => ['foo', 'bar'],
            'properties' => (object)[
                'foo' => (object)[
                    'type'       => 'object',
                    'properties' => (object)[
                        'blah' => (object)[
                            'type' => 'integer'
                        ]
                    ]
                ],
                'bar' => (object)[
                    'type'       => 'object',
                    'required'   => ['blah'],
                    'properties' => (object)[
                        'blah' => (object)[
                            'type' => 'string'
                        ]
                    ]
                ]
            ]
        ];
        $validator->check($value, $schema);
        $errors = $validator->getErrors();

        $exception = new InvalidParametersException('Nope', $errors);

        $mock = $this->refBuilder;
        /** @var \PHPUnit_Framework_MockObject_MockObject $mock */
        $mock
            ->expects($this->exactly(2))
            ->method('buildSpecificationLink')
            ->willReturnOnConsecutiveCalls('http://1.net/1', 'http://2.net/2');

        $vndError = $this->factory->create(
            $this->createSimpleRequest(),
            $exception
        );

        $resources = $vndError->getResources();
        $this->assertArrayHasKey('errors', $resources);
        $errorResources = $resources['errors'];
        $this->assertSame(count($errors), count($errorResources));

        $resources = array_values($errorResources);

        foreach ($errors as $i => $spec) {
            $data = $resources[$i]->getData();
            $this->assertContains($spec['message'], $data['message']);
        }
    }

    /**
     * @return Request
     */
    private function createSimpleRequest()
    {
        return new Request;
    }
}
