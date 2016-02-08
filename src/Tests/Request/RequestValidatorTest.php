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
}
