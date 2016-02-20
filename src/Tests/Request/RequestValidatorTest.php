<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use KleijnWeb\SwaggerBundle\Request\RequestValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canValidateDate()
    {
        $dateTime = new \DateTimeImmutable();
        $this->runTimeTest('date', '2015-12-12', $dateTime);
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function canInvalidateDate()
    {
        $this->runTimeTest('date', '2016-01-01T00:00:00Z', '2016-01-01T00:00:00Z');
        $dateTime = new \DateTimeImmutable();
        $this->runTimeTest('date', '2016-01-01T00:00:00Z', $dateTime);
    }

    /**
     * @test
     */
    public function canValidateDateTime()
    {
        $dateTime = new \DateTimeImmutable();
        $this->runTimeTest('date-time', $dateTime->format(\DateTime::W3C), $dateTime);
        $this->runTimeTest('date-time', '2016-01-01T00:00:00Z', '2016-01-01T00:00:00Z');
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function canInvalidateDateTime()
    {
        $this->runTimeTest('date-time', 'm-d-Y', new \DateTimeImmutable());
        $this->runTimeTest('date-time', '01-01-2014T00:00:00Z', new \DateTimeImmutable());
    }

    /**
     * @test
     */
    public function canOmitParameterWhenNotExplicitlyMarkedAsRequired()
    {
        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    'name'   => 'foo',
                    'in'     => 'body',
                    'schema' => (object)[
                        'type' => 'integer'
                    ]
                ]
            ]
        ];
        $validator = new RequestValidator(OperationObject::createFromOperationDefinition($operationDefinition));
        $request = new Request();
        $validator->validateRequest($request);
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function cannotOmitParameterWhenExplicitlyMarkedAsRequired()
    {
        $request = new Request();

        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    'name'     => 'foo',
                    'required' => true,
                    'in'       => 'query',
                    'type'     => 'int'
                ]
            ]
        ];
        $validator = new RequestValidator(OperationObject::createFromOperationDefinition($operationDefinition));
        $validator->validateRequest($request);
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function cannotOmitBodyWhenExplicitlyMarkedAsRequired()
    {
        $request = new Request();

        $operationDefinition = (object)[
            'parameters' => [
                (object)[
                    'name'     => 'foo',
                    'required' => true,
                    'in'       => 'query',
                    'type'     => 'int'
                ]
            ]
        ];
        $validator = new RequestValidator(OperationObject::createFromOperationDefinition($operationDefinition));
        $validator->validateRequest($request);
    }

    /**
     * @param string                    $format
     * @param string                    $rawDateTime
     * @param \DateTimeImmutable|string $dateTime
     *
     * @throws \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    private function runTimeTest($format, $rawDateTime, $dateTime)
    {
        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'header' => 'headers'
        ];

        $parameterName = 'time';

        foreach (['query', 'path', 'header'] as $source) {
            $operationDefinition = (object)[
                'parameters' => [
                    (object)[
                        'name'   => $parameterName,
                        'in'     => $source,
                        'type'   => 'string',
                        'format' => $format
                    ]
                ]
            ];

            $validator = new RequestValidator(OperationObject::createFromOperationDefinition($operationDefinition));
            $request = new Request();
            $bagName = $paramBagMapping[$source];
            $request->$bagName->set($parameterName, $rawDateTime);
            $request->attributes->set($parameterName, $dateTime);
            $validator->validateRequest($request);
        }
    }
}
