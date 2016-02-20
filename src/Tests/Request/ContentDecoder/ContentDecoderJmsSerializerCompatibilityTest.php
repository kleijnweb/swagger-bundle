<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder;

use JMS\Serializer\Serializer;
use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\Request\ContentDecoder;
use KleijnWeb\SwaggerBundle\Serializer\JmsSerializerFactory;
use KleijnWeb\SwaggerBundle\Serializer\SerializationTypeResolver;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use KleijnWeb\SwaggerBundle\Tests\Request\TestRequestFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ContentDecoderJmsSerializerCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentDecoder
     */
    private $contentDecoder;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Create serializer
     */
    protected function setUp()
    {
        $this->serializer = new SerializerAdapter(JmsSerializerFactory::factory());
        $this->contentDecoder = new ContentDecoder(
            $this->serializer,
            new SerializationTypeResolver('KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder')
        );
    }

    /**
     * @test
     */
    public function canDeserializeIntoObject()
    {
        $content = [
            'foo' => 'bar'
        ];
        $request = new Request([], [], [], [], [], [], json_encode($content));
        $request->headers->set('Content-Type', 'application/json');


        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    "in"     => "body",
                    "name"   => "body",
                    "schema" => (object)[
                        '$ref' => "#/definitions/JmsAnnotatedResourceStub"
                    ]
                ]
            ]
        ];

        $operationObject = OperationObject::createFromOperationDefinition((object)$operationDefinition);

        $actual = $this->contentDecoder->decodeContent($request, $operationObject);

        $className = 'KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder\JmsAnnotatedResourceStub';
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
        $request = TestRequestFactory::create($content);
        $request->headers->set('Content-Type', 'application/json');

        $operationObject = OperationObject::createFromOperationDefinition((object)[]);
        $this->contentDecoder->decodeContent($request, $operationObject);
    }

    /**
     * @test
     * @dataProvider contentTypeProvider
     *
     * @param string $contentType
     */
    public function willAlwaysDecodeJson($contentType)
    {
        $content = '{ "foo": "bar" }';
        $request = TestRequestFactory::create($content);
        $request->headers->set('Content-Type', $contentType);

        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    "in"     => "body",
                    "name"   => "body",
                    "schema" => (object)[
                        '$ref' => "#/definitions/JmsAnnotatedResourceStub"
                    ]
                ]
            ]
        ];

        $operationObject = OperationObject::createFromOperationDefinition((object)$operationDefinition);

        $actual = $this->contentDecoder->decodeContent($request, $operationObject);
        $className = 'KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder\JmsAnnotatedResourceStub';
        $expected = (new $className)->setFoo('bar');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function contentTypeProvider()
    {
        return [
            ['application/json'],
            ['application/vnd.api+json']
        ];
    }
}
