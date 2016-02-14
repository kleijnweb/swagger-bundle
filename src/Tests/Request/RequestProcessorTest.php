<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\Request\RequestCoercer;
use KleijnWeb\SwaggerBundle\Request\RequestProcessor;
use KleijnWeb\SwaggerBundle\Request\RequestValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willValidateRequest()
    {
        /** @var RequestValidator $contentDecoderMock */
        $validatorMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\RequestValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    'name' => 'find',
                    'in'   => 'query'
                ]
            ]
        ];

        $operationObject = OperationObject::createFromOperationDefinition((object)$operationDefinition);

        $request = new Request();
        $validatorMock
            ->expects($this->once())
            ->method('validateRequest')
            ->with($request);
        $validatorMock
            ->expects($this->once())
            ->method('setOperationObject')
            ->with($operationObject);

        /** @var RequestCoercer $contentDecoderMock */
        $coercerMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\RequestCoercer')
            ->disableOriginalConstructor()
            ->getMock();

        $processor = new RequestProcessor($validatorMock, $coercerMock);

        $processor->process($request, $operationObject);
    }

    /**
     * @test
     */
    public function willCoerceRequest()
    {
        /** @var RequestValidator $contentDecoderMock */
        $validatorMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\RequestValidator')
            ->disableOriginalConstructor()
            ->getMock();


        /** @var RequestCoercer $contentDecoderMock */
        $coercerMock = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Request\RequestCoercer')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $coercerMock
            ->expects($this->once())
            ->method('coerceRequest')
            ->with($request);

        $processor = new RequestProcessor($validatorMock, $coercerMock);

        $operationDefinition = [
            'parameters' => (object)[
                (object)[
                    'name' => 'myContent',
                    'in'   => 'body'
                ]
            ]
        ];

        $operationObject = OperationObject::createFromOperationDefinition((object)$operationDefinition);

        $processor->process($request, $operationObject);
    }
}
