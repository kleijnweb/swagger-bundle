<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Document\Specification\Operation;
use KleijnWeb\SwaggerBundle\Request\ContentDecoder;
use KleijnWeb\SwaggerBundle\Serialize\Serializer;
use KleijnWeb\SwaggerBundle\Serialize\Serializer\ArraySerializer;
use KleijnWeb\SwaggerBundle\Tests\Request\TestRequestFactory;

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
     * @var ArraySerializer
     */
    private $serializer;

    /**
     * Set content decoder with default serializer
     */
    protected function setUp()
    {
        $documentRepository = $this
            ->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $documentRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn(new Specification(new \stdClass));

        $this->serializer = $mock = $this->getMockBuilder(Serializer::class)->getMock();

        $mock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(function (array $value) {
                return json_encode($value);
            });

        $mock->expects($this->any())
            ->method('deserialize')
            ->willReturnCallback(function (string $value) {
                $array = json_decode($value, true);

                if (!is_array($array)) {
                    throw new \UnexpectedValueException("Expected result to be an array");
                }

                return $array;
            });

        $documentRepository = $this
            ->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $documentRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn(new Specification(new \stdClass));

        /** @noinspection PhpParamsInspection */
        $this->contentDecoder = new ContentDecoder($this->serializer, $documentRepository);
    }

    /**
     * @test
     */
    public function canDecodeValidJson()
    {
        $content = '{ "foo": "bar" }';
        $request = TestRequestFactory::create($content, [], 'faux');
        $request->headers->set('Content-Type', 'application/json');

        $operationObject = Operation::createFromOperationDefinition((object)[]);

        $actual   = $this->contentDecoder->decodeContent($request, $operationObject);
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
        $request = TestRequestFactory::create($content, [], 'faux');
        $request->headers->set('Content-Type', 'application/json');

        $operationObject = Operation::createFromOperationDefinition((object)[]);

        $this->contentDecoder->decodeContent($request, $operationObject);
    }
}
