<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener\Request;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Path;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ObjectSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\ValidationResult;
use KleijnWeb\PhpApi\Descriptions\Request\RequestParameterAssembler;
use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestProcessor;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestProcessorTest extends TestCase
{
    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $hydratorMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $parametersAssemblerMock;

    /**
     * Create mocks
     */
    protected function setUp()
    {
        /** @var Repository $repository */
        $this->repositoryMock = $repository = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Repository $repository */
        $this->validatorMock = $validator = $this
            ->getMockBuilder(SchemaValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var RequestParameterAssembler $hydrator */
        $this->parametersAssemblerMock = $parametersAssembler = $this
            ->getMockBuilder(RequestParameterAssembler::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ObjectHydrator $hydrator */
        $this->hydratorMock = $hydrator = $this
            ->getMockBuilder(ObjectHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function willThrowExceptionIfRequestDoesNotHaveDocumentUri()
    {
        $processor = $this->createProcessor();

        $this->expectException(\UnexpectedValueException::class);
        $processor->process(new Request());
    }

    /**
     * @test
     */
    public function willThrowExceptionWhenContentIsNotJson()
    {
        $processor = $this->createProcessor();

        $this->expectException(MalformedContentException::class);

        $processor->process(
            $this->createRequest(
                [
                    RequestMeta::ATTRIBUTE_URI  => '/uri',
                    RequestMeta::ATTRIBUTE_PATH => '/path',
                ],
                'not json'
            )
        );
    }

    /**
     * @test
     */
    public function willAssembleParameters()
    {
        $processor = $this->createProcessor();
        $this->parametersAssemblerMock->expects($this->once())->method('assemble');

        $processor->process(
            $this->createRequest(
                [
                    RequestMeta::ATTRIBUTE_URI  => '/uri',
                    RequestMeta::ATTRIBUTE_PATH => '/path',
                ]
            )
        );
    }

    /**
     * @test
     */
    public function willUpdateAttributes()
    {
        $processor         = $this->createProcessor();
        $coercedAttributes = (object)[
            'foo' => 'bar',
        ];

        $this->parametersAssemblerMock->expects($this->once())->method('assemble')->willReturn($coercedAttributes);

        $request = $this->createRequest(
            [
                RequestMeta::ATTRIBUTE_URI  => '/uri',
                RequestMeta::ATTRIBUTE_PATH => '/path',
            ]
        );

        $processor->process($request);

        $this->assertTrue($request->attributes->has(RequestMeta::ATTRIBUTE_URI));
        $this->assertTrue($request->attributes->has(RequestMeta::ATTRIBUTE_PATH));
        $this->assertTrue($request->attributes->has('foo'));
        $this->assertSame('bar', $request->attributes->get('foo'));
    }

    /**
     * @test
     */
    public function canDecodeJsonBody()
    {
        $body = (object)['foo' => 'bar'];

        $processor = $this->createProcessor();

        $this->parametersAssemblerMock
            ->expects($this->once())
            ->method('assemble')
            ->willReturnCallback(
                function (
                    Operation $operation,
                    array $query,
                    array $attributes,
                    array $headers,
                    \stdClass $body
                ) {
                    return (object)['theBody' => $body];
                }
            );

        $request = $this->createRequest(
            [
                RequestMeta::ATTRIBUTE_URI  => '/uri',
                RequestMeta::ATTRIBUTE_PATH => '/path',
            ],
            json_encode($body)
        );

        $processor->process($request);

        $this->assertEquals($body, $request->attributes->get('theBody'));
    }

    /**
     * @test
     */
    public function canHydrateJsonBody()
    {
        $body = (object)['theBody' => 'bar'];

        $processor = $this->createProcessor(true, true);

        $parameter       = new Parameter('theBody', true, new ObjectSchema((object)[]), Parameter::IN_BODY);
        $descriptionMock = $this->getMockBuilder(Description::class)->disableOriginalConstructor()->getMock();
        $descriptionMock->expects($this->once())->method('getRequestBodyParameter')->willReturn($parameter);

        $this->repositoryMock->expects($this->once())->method('get')->willReturn($descriptionMock);

        $this->parametersAssemblerMock
            ->expects($this->once())
            ->method('assemble')
            ->willReturnCallback(
                function (
                    Operation $operation,
                    array $query,
                    array $attributes,
                    array $headers,
                    \stdClass $body
                ) {
                    return (object)['theBody' => $body];
                }
            );

        $dto = new \ArrayObject;

        $this->hydratorMock
            ->expects($this->once())
            ->method('hydrate')
            ->with($body, $this->isInstanceOf(Schema::class))
            ->willReturnCallback(
                function () use ($dto) {
                    return $dto;
                }
            );

        $request = $this->createRequest(
            [
                RequestMeta::ATTRIBUTE_URI  => '/uri',
                RequestMeta::ATTRIBUTE_PATH => '/path',
            ],
            json_encode($body)
        );

        $processor->process($request);

        $this->assertSame($dto, $request->attributes->get('theBody'));
    }

    /**
     * @test
     */
    public function willSetRequestMetaAttribute()
    {
        $processor         = $this->createProcessor();
        $coercedAttributes = (object)[
            'foo' => 'bar',
        ];

        $this->parametersAssemblerMock->expects($this->once())->method('assemble')->willReturn($coercedAttributes);

        $request = $this->createRequest(
            [
                RequestMeta::ATTRIBUTE_URI  => '/uri',
                RequestMeta::ATTRIBUTE_PATH => '/path',
            ]
        );

        $processor->process($request);

        $this->assertTrue($request->attributes->has(RequestMeta::ATTRIBUTE));
    }

    /**
     * @test
     */
    public function willThrowExceptionIfRequestIsNotValid()
    {
        $processor = $this->createProcessor(false, false);

        $this->expectException(ValidationException::class);

        $processor->process(
            $this->createRequest(
                [
                    RequestMeta::ATTRIBUTE_URI  => '/uri',
                    RequestMeta::ATTRIBUTE_PATH => '/path',
                ]
            )
        );
    }

    /**
     * @test
     */
    public function willThrowExceptionIfRequestBodyIsNotValid()
    {
        $this->hydratorMock->expects($this->never())
            ->method('hydrate');

        $processor = $this->createProcessor(true, false);
        $this->expectException(ValidationException::class);

        $processor->process(
            $this->createRequest(
                [
                    RequestMeta::ATTRIBUTE_URI  => '/uri',
                    RequestMeta::ATTRIBUTE_PATH => '/path',
                ],
                json_encode([])
            )
        );
    }

    /**
     * @test
     */
    public function willPassNullToValidatorWhenOperationAndRequestHaveNoParams()
    {
        $processor = $this->createProcessor();

        $descriptionMock = $this->getMockBuilder(Description::class)->disableOriginalConstructor()->getMock();
        $pathMock        = $this->getMockBuilder(Path::class)->disableOriginalConstructor()->getMock();
        $descriptionMock->expects($this->once())->method('getPath')->willReturn($pathMock);
        $operationMock = $this->getMockBuilder(Operation::class)->disableOriginalConstructor()->getMock();
        $pathMock->expects($this->once())->method('getOperation')->willReturn($operationMock);
        $operationMock->expects($this->once())->method('hasParameters')->willReturn(false);

        $this->repositoryMock->expects($this->once())->method('get')->willReturn($descriptionMock);
        $this->parametersAssemblerMock->expects($this->once())->method('assemble')->willReturn((object)[]);

        $this->validatorMock->expects($this->once())->method('validate')->with(
            $this->isInstanceOf(Schema::class),
            null
        );

        $processor->process(
            $this->createRequest(
                [
                    RequestMeta::ATTRIBUTE_URI  => '/uri',
                    RequestMeta::ATTRIBUTE_PATH => '/path',
                ]
            )
        );
    }

    /**
     * @param bool      $useHydrator
     * @param null|bool $forcedValidationResult
     *
     * @return RequestProcessor
     */
    private function createProcessor(bool $useHydrator = false, $forcedValidationResult = true): RequestProcessor
    {
        if (null !== $forcedValidationResult) {
            $this->validatorMock
                ->expects($this->any())
                ->method('validate')
                ->willReturn(new ValidationResult($forcedValidationResult));
        }

        /** @var Repository $repository */
        $repository = $this->repositoryMock;
        /** @var SchemaValidator $validator */
        $validator = $this->validatorMock;
        /** @var RequestParameterAssembler $parametersAssembler */
        $parametersAssembler = $this->parametersAssemblerMock;
        /** @var ObjectHydrator $hydrator */
        $hydrator = $this->hydratorMock;

        return new RequestProcessor(
            $repository,
            $validator,
            $parametersAssembler,
            ($useHydrator ? $hydrator : null)
        );
    }

    /**
     * @param array  $attributes
     *
     * @param string $content
     *
     * @return Request
     */
    private function createRequest(array $attributes, string $content = ''): Request
    {
        return new class($attributes, $content) extends Request
        {
            /**
             * @param array $attributes
             * @param array $content
             */
            public function __construct(array $attributes, $content)
            {
                parent::__construct();
                $this->attributes = new ParameterBag($attributes);
                $this->content    = $content;
            }
        };
    }
}
