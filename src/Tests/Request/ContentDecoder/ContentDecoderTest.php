<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\Request\ContentDecoder;
use KleijnWeb\SwaggerBundle\Serializer\ArraySerializer;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use KleijnWeb\SwaggerBundle\Tests\Request\TestRequestFactory;
use Symfony\Component\HttpFoundation\Request;

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
     * @var SerializerAdapter
     */
    private $serializer;

    /**
     * Set content decoder with default serializer
     */
    protected function setUp()
    {
        $this->serializer = new SerializerAdapter(new ArraySerializer());
        $this->contentDecoder = new ContentDecoder($this->serializer);
    }

    /**
     * @test
     */
    public function canDecodeValidJson()
    {
        $content = '{ "foo": "bar" }';
        $request = TestRequestFactory::create($content);
        $request->headers->set('Content-Type', 'application/json');

        $operationObject = OperationObject::createFromOperationDefinition((object)[]);

        $actual = $this->contentDecoder->decodeContent($request, $operationObject);
        $expected = ['foo' => 'bar'];
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     *
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\MalformedContentException
     */
    public function willThrowMalformedContentExceptionWhenDecodingFails()
    {
        $content = 'NOT VALID JSON';
        $request = TestRequestFactory::create($content);
        $request->headers->set('Content-Type', 'application/json');

        $operationObject = OperationObject::createFromOperationDefinition((object)[]);

        $this->contentDecoder->decodeContent($request, $operationObject);
    }
}
