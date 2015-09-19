<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request;

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
        $operationDefinition = [
            'parameters' => [
                [
                    'name' => 'foo',
                    'in'   => 'body',
                    'type' => 'integer'
                ]
            ]
        ];
        $validator = new RequestValidator($operationDefinition);
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

        $operationDefinition = [
            'parameters' => [
                [
                    'name'     => 'foo',
                    'required' => true,
                    'in'       => 'query',
                    'type'     => 'int'
                ]
            ]
        ];
        $validator = new RequestValidator($operationDefinition);
        $validator->validateRequest($request);
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\InvalidParametersException
     */
    public function cannotOmitBodyWhenExplicitlyMarkedAsRequired()
    {
        $request = new Request();

        $operationDefinition = [
            'parameters' => [
                [
                    'name'     => 'foo',
                    'required' => true,
                    'in'       => 'query',
                    'type'     => 'int'
                ]
            ]
        ];
        $validator = new RequestValidator($operationDefinition);
        $validator->validateRequest($request);
    }
}
