<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request;

use KleijnWeb\SwaggerBundle\Request\RequestTransformer;
use KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willDecodeContent()
    {
        $this->contentDecoderMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentDecoderMock
            ->expects($this->any())
            ->method('decodeContent')
            ->willReturnCallback(function (Request $request) {
                $data = json_decode($request->getContent());
                if (is_null($data)) {
                    throw new \Exception("failed to json_decode '$string'");
                }

                return $data;
            });

        $transformer = new RequestTransformer($this->contentDecoderMock);
        $content = '[]';
        $request = new Request([], [], [], [], [], [], $content);

        $operationDefinition = [
            'parameters' => []
        ];

        $transformer->coerceRequest($request, $operationDefinition);

        $this->assertSame([], $request->getContent());
    }

    /**
     * @test
     */
    public function canOmitParameterWhenNotExplicitlyMarkedAsRequired()
    {
        $this->contentDecoderMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentDecoderMock
            ->expects($this->any())
            ->method('decodeContent')
            ->willReturnCallback(function (Request $request) {
                $data = json_decode($request->getContent());
                if (is_null($data)) {
                    throw new \Exception("failed to json_decode '$string'");
                }

                return $data;
            });

        $transformer = new RequestTransformer($this->contentDecoderMock);
        $content = '[]';
        $request = new Request([], [], [], [], [], [], $content);

        $operationDefinition = [
            'parameters' => [
                [
                    'name' => 'foo',
                    'in'   => 'query',
                    'type' => 'integer'
                ]
            ]
        ];

        $transformer->coerceRequest($request, $operationDefinition);

        $this->assertSame([], $request->getContent());
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function cannotOmitParameterWhenExplicitlyMarkedAsRequired()
    {
        $this->contentDecoderMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentDecoderMock
            ->expects($this->any())
            ->method('decodeContent')
            ->willReturnCallback(function (Request $request) {
                $data = json_decode($request->getContent());
                if (is_null($data)) {
                    throw new \Exception("failed to json_decode '$string'");
                }

                return $data;
            });

        $transformer = new RequestTransformer($this->contentDecoderMock);
        $content = '[]';
        $request = new Request([], [], [], [], [], [], $content);

        $operationDefinition = [
            'parameters' => [
                [
                    'name'     => 'foo',
                    'required' => true,
                    'in'       => 'query',
                    'type'     => 'integer'
                ]
            ]
        ];

        $transformer->coerceRequest($request, $operationDefinition);

        $this->assertSame([], $request->getContent());
    }
}
