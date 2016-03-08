<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\Request\ContentDecoder;
use KleijnWeb\SwaggerBundle\Request\RequestCoercer;
use KleijnWeb\SwaggerBundle\Request\ParameterCoercer;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestCoercerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentDecoder
     */
    private $contentDecoderMock;

    protected function setUp()
    {
        $this->contentDecoderMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\ContentDecoder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentDecoderMock
            ->expects($this->any())
            ->method('decodeContent')
            ->willReturnCallback(function (Request $request) {
                return json_decode($request->getContent());
            });
    }

    /**
     * @test
     */
    public function willAddDecodedContentAsAttribute()
    {
        $coercer = new RequestCoercer($this->contentDecoderMock);
        $content = '[1,2,3,4]';
        $request = TestRequestFactory::create($content);

        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    'name'   => 'myContent',
                    'in'     => 'body',
                    'schema' => (object)[
                        'type' => 'array'
                    ]
                ]
            ]
        ];

        $operationObject = OperationObject::createFromOperationDefinition((object)$operationDefinition);
        $coercer->coerceRequest($request, $operationObject);

        $this->assertSame([1, 2, 3, 4], $request->attributes->get('myContent'));
    }

    /**
     * @test
     */
    public function willConstructDate()
    {
        $coercer = new RequestCoercer($this->contentDecoderMock);
        $request = TestRequestFactory::create(null, ['foo' => "2015-01-01"]);

        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    'name'   => 'foo',
                    'in'     => 'query',
                    'type'   => 'string',
                    'format' => 'date'
                ]
            ]
        ];

        $operationObject = OperationObject::createFromOperationDefinition((object)$operationDefinition);
        $coercer->coerceRequest($request, $operationObject);

        $expected = ParameterCoercer::coerceParameter($operationDefinition->parameters[0], "2015-01-01");

        // Sanity check
        $this->assertInstanceOf('DateTime', $expected);

        $this->assertEquals($expected, $request->attributes->get('foo'));
    }

    /**
     * @test
     */
    public function willNotFailWithNoParameters()
    {
        $coercer = new RequestCoercer($this->contentDecoderMock);
        $content = '[1,2,3,4]';
        $request = TestRequestFactory::create($content);

        $operationDefinition = (object)[];

        $operationObject = OperationObject::createFromOperationDefinition((object)$operationDefinition);
        $coercer->coerceRequest($request, $operationObject);

        $this->assertEmpty($request->attributes);
    }
}
