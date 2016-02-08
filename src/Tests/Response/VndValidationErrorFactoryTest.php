<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Response;

use JsonSchema\Validator;
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

    protected function setUp()
    {
        $this->factory = new VndValidationErrorFactory();
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
        $vndError = $this->factory->create(
            $this->createSimpleRequest(),
            $exception
        );
        $this->assertSame(count($errors), count($vndError->getResources()));

        $resources = array_values($vndError->getResources());

        foreach ($errors as $i => $spec) {
            $this->assertSame($spec['message'], $resources[$i][0]->getMessage());
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
