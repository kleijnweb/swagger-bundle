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
use KleijnWeb\SwaggerBundle\Serialize\SerializationTypeResolver;
use KleijnWeb\SwaggerBundle\Serialize\Serializer\Factory\SymfonySerializerFactory;
use KleijnWeb\SwaggerBundle\Serialize\Serializer\SymfonySerializerAdapter;
use KleijnWeb\SwaggerBundle\Tests\Request\TestRequestFactory;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ContentDecoderSymfonySerializerCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    const FAUX_CLASS_NAME = 'ContentDecoderSymfonySerializerCompatibilityTestFauxClass';

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
            ->getMockBuilder(DecoderInterface::class)
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

        $this->serializer = new SymfonySerializerAdapter(SymfonySerializerFactory::factory($this->jsonDecoderMock));

        $typeResolver = $this
            ->getMockBuilder(SerializationTypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $typeResolver
            ->expects($this->any())
            ->method('resolveOperationBodyType')
            ->willReturn(self::FAUX_CLASS_NAME);

        $documentRepository = $this
            ->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $documentRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn(new Specification(new \stdClass));

        /** @noinspection PhpParamsInspection */
        $this->contentDecoder = new ContentDecoder($this->serializer, $documentRepository, $typeResolver);
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

        $request = TestRequestFactory::create(json_encode($content), [], 'faux');
        $request->headers->set('Content-Type', 'application/json');

        $className = self::FAUX_CLASS_NAME;

        eval("
            class $className {
                public function setFoo(\$foo){ \$this->foo = \$foo; return \$this;}
                public function getFoo(){ return \$this->foo; }
            }
        ");

        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    "in"     => "body",
                    "name"   => "body",
                    "schema" => (object)[
                        '$ref' => "#/definitions/$className"
                    ]
                ]
            ]
        ];

        $operationObject = Operation::createFromOperationDefinition((object)$operationDefinition);

        $actual = $this->contentDecoder->decodeContent($request, $operationObject);

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
        $request = TestRequestFactory::create($content, [], 'faux');
        $request->headers->set('Content-Type', 'application/json');

        $this->jsonDecoderMock
            ->expects($this->once())
            ->method('decode')
            ->with($request->getContent(), 'json');

        $operationObject = Operation::createFromOperationDefinition((object)[]);

        $this->contentDecoder->decodeContent($request, $operationObject);
    }
}
