<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\Transformer;

use KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ContentDecoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentDecoder
     */
    private $contentDecoder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonDecoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * Set up mocks
     */
    protected function setUp()
    {
        $this->jsonDecoderMock = $this
            ->getMockBuilder('Symfony\Component\Serializer\Encoder\DecoderInterface')
            ->getMockForAbstractClass();
        $this->jsonDecoderMock
            ->expects($this->any())
            ->method('decode')
            ->willReturnCallback(function ($string) {
                $data = json_decode($string);
                if (is_null($data)) {
                    throw new \Exception();
                }
            });

        $this->jsonDecoderMock
            ->expects($this->any())
            ->method('supportsDecoding')
            ->willReturn(true);

        $this->serializer = new Serializer([], [$this->jsonDecoderMock]);
        $this->contentDecoder = new ContentDecoder($this->serializer);
    }

    /**
     * @test
     */
    public function willPassCorrectParametersToSerializer()
    {
        $content = json_encode([1, 2, 3]);
        $request = new Request([], [], [], [], [], [], $content);
        $request->headers->set('Content-Type', 'application/json');

        $operationDefinition = [];
        $this->jsonDecoderMock
            ->expects($this->once())
            ->method('decode')
            ->with($request->getContent(), 'json');

        $this->contentDecoder->decodeContent($request, $operationDefinition);
    }

    /**
     * @test
     *
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\MalformedContentException
     */
    public function willThrowMalformedContentExceptionWhenDecodingFails()
    {
        $content = 'lkjhlkj';
        $request = new Request([], [], [], [], [], [], $content);
        $request->headers->set('Content-Type', 'application/json');

        $operationDefinition = [];
        $this->jsonDecoderMock
            ->expects($this->once())
            ->method('decode')
            ->with($request->getContent(), 'json');

        $this->contentDecoder->decodeContent($request, $operationDefinition);
    }
}
