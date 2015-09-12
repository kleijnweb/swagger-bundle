<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\Transformer;

use KleijnWeb\SwaggerBundle\Request\Transformer\ContentDecoder;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use KleijnWeb\SwaggerBundle\Serializer\SymfonySerializerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ContentDecoderSymfonySerializerCompatibilityTest extends \PHPUnit_Framework_TestCase
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
                return $data;
            });
        $this->jsonDecoderMock
            ->expects($this->any())
            ->method('supportsDecoding')
            ->willReturn(true);

        $this->serializer = new SerializerAdapter(SymfonySerializerFactory::factory($this->jsonDecoderMock));
        $this->contentDecoder = new ContentDecoder($this->serializer);
    }

    /**
     * @test
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    public function canDeserializeIntoObject()
    {
        $content = [
            'foo' => 'bar'
        ];
        $request = new Request([], [], [], [], [], [], json_encode($content));
        $request->headers->set('Content-Type', 'application/json');

        $className = 'CanDeserializeObject';
        $number = 0;
        while (class_exists($className)) {
            $className .= ++$number;
        }

        eval("
            class $className {
                public function setFoo(\$foo){ \$this->foo = \$foo; return \$this;}
                public function getFoo(){ return \$this->foo; }
            }
        ");

        $operationDefinition = [
            'parameters' => [
                [
                    "in"       => "body",
                    "name"     => "body",
                    "schema"   => [
                        '$ref' => "#/definitions/$className"
                    ]
                ]
            ]
        ];

        $actual = $this->contentDecoder->decodeContent($request, $operationDefinition);

        $expected = (new $className)->setFoo('bar');

        $this->assertEquals($expected, $actual);
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
