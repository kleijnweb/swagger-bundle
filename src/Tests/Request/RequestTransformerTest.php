<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request;

use KleijnWeb\SwaggerBundle\Request\RequestProcessor;
use KleijnWeb\SwaggerBundle\Request\ParameterCoercer;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willAddDecodedContentAsAttribute()
    {
        $this->contentDecoderMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\ContentDecoder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentDecoderMock
            ->expects($this->any())
            ->method('decodeContent')
            ->willReturnCallback(function (Request $request) {
                $data = json_decode($request->getContent());
                if (is_null($data)) {
                    throw new \Exception("Failed to json_decode '{$request->getContent()}'");
                }

                return $data;
            });

        $transformer = new RequestProcessor($this->contentDecoderMock);
        $content = '[1,2,3,4]';
        $request = new Request([], [], [], [], [], [], $content);

        $operationDefinition = [
            'parameters' => [
                [
                    'name' => 'myContent',
                    'in'   => 'body'
                ]
            ]
        ];

        $transformer->coerceRequest($request, $operationDefinition);

        $this->assertSame([1, 2, 3, 4], $request->attributes->get('myContent'));
    }

    /**
     * @test
     */
    public function canOmitParameterWhenNotExplicitlyMarkedAsRequired()
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

        $transformer = new RequestProcessor($this->contentDecoderMock);

        $request = new Request();

        $operationDefinition = [
            'parameters' => [
                [
                    'name' => 'foo',
                    'in'   => 'body',
                    'type' => 'integer'
                ]
            ]
        ];

        $transformer->coerceRequest($request, $operationDefinition);

        $this->assertFalse($request->attributes->has('foo'));
    }

    /**
     * @test
     */
    public function willConstructDate()
    {
        $this->contentDecoderMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\ContentDecoder')
            ->disableOriginalConstructor()
            ->getMock();

        $transformer = new RequestProcessor($this->contentDecoderMock);
        $request = new Request(['foo' => "2015-01-01"], [], [], [], [], []);

        $operationDefinition = [
            'parameters' => [
                [
                    'name'   => 'foo',
                    'in'     => 'query',
                    'type'   => 'string',
                    'format' => 'date'
                ]
            ]
        ];

        $transformer->coerceRequest($request, $operationDefinition);

        $expected = ParameterCoercer::coerceParameter($operationDefinition['parameters'][0], "2015-01-01");

        // Sanity check
        $this->assertInstanceOf('DateTime', $expected);

        $this->assertEquals($expected, $request->attributes->get('foo'));
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function cannotOmitParameterWhenExplicitlyMarkedAsRequired()
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

        $transformer = new RequestProcessor($this->contentDecoderMock);
        $request = new Request();

        $operationDefinition = [
            'parameters' => [
                [
                    'name'     => 'foo',
                    'required' => true,
                    'in'       => 'query',
                    'type'     => 'int'
                ]
            ]
        ];

        $transformer->coerceRequest($request, $operationDefinition);
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function cannotOmitBodyWhenExplicitlyMarkedAsRequired()
    {
        $this->contentDecoderMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\ContentDecoder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentDecoderMock
            ->expects($this->any())
            ->method('decodeContent')
            ->willReturnCallback(function (Request $request) {
                $data = json_decode($request->getContent());
                if (is_null($data)) {
                    throw new \Exception("Failed to json_decode '{$request->getContent()}'");
                }

                return $data;
            });

        $transformer = new RequestProcessor($this->contentDecoderMock);
        $content = '[]';
        $request = new Request([], [], [], [], [], [], $content);

        $operationDefinition = [
            'parameters' => [
                [
                    'name'     => 'foo',
                    'required' => true,
                    'in'       => 'body',
                    'type'     => 'array'
                ]
            ]
        ];

        $transformer->coerceRequest($request, $operationDefinition);
    }
}
