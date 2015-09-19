<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder;

use KleijnWeb\SwaggerBundle\Request\ContentDecoder;
use KleijnWeb\SwaggerBundle\Serializer\ArraySerializer;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
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
        $request = new Request([], [], [], [], [], [], $content);
        $request->headers->set('Content-Type', 'application/json');

        $operationDefinition = [];

        $actual = $this->contentDecoder->decodeContent($request, $operationDefinition);
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
        $request = new Request([], [], [], [], [], [], $content);
        $request->headers->set('Content-Type', 'application/json');

        $operationDefinition = [];

        $this->contentDecoder->decodeContent($request, $operationDefinition);
    }
}
