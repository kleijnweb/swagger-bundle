<?php
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
        $dateTime = new \DateTime();
        $this->runStringTest($dateTime, '2015-12-12', 'date');
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function canInvalidateDate()
    {
        $this->runStringTest('2016-01-01T00:00:00Z', '2016-01-01T00:00:00Z', 'date');
        $dateTime = new \DateTime();
        $this->runStringTest($dateTime, '2016-01-01T00:00:00Z', 'date');
    }

    /**
     * @test
     */
    public function canValidateDateTime()
    {
        $dateTime = new \DateTime();
        $this->runStringTest($dateTime, $dateTime->format(\DateTime::W3C), 'date-time');
        $this->runStringTest('2016-01-01T00:00:00Z', '2016-01-01T00:00:00Z', 'date-time');
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function canInvalidateDateTime()
    {
        $this->runStringTest(new \DateTime(), 'm-d-Y', 'date-time');
        $this->runStringTest(new \DateTime(), '01-01-2014T00:00:00Z', 'date-time');
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function canUseAdditionalJsonSchemaConstraints()
    {
        $value = str_repeat('f', 100);

        $this->runStringTest(null, $value, $value, ['maxLength' => 99]);
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
        $request   = new Request();
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
     * @param mixed  $value
     * @param string $rawValue
     * @param string $format
     *
     * @param array  $additionalParameterProperties
     *
     * @throws \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    private function runStringTest($value, $rawValue, $format, array $additionalParameterProperties = [])
    {
        $paramBagMapping = [
            'query'  => 'query',
            'path'   => 'attributes',
            'header' => 'headers'
        ];

        $parameterName = 'test';

        foreach (['query', 'path', 'header'] as $source) {
            $parameterDefinition = array_merge(
                $additionalParameterProperties,
                [
                    'name'   => $parameterName,
                    'in'     => $source,
                    'type'   => 'string',
                    'format' => $format
                ]
            );

            $operationDefinition = (object)[
                'parameters' => [
                    (object)$parameterDefinition
                ]
            ];

            $validator = new RequestValidator(OperationObject::createFromOperationDefinition($operationDefinition));
            $request   = new Request();
            $bagName   = $paramBagMapping[$source];

            $request->$bagName->set($parameterName, $rawValue);
            $request->attributes->set($parameterName, $value);
            $validator->validateRequest($request);
        }
    }
}
