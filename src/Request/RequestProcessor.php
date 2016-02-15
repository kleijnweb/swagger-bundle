<?php
declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Symfony\Component\HttpFoundation\Request;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestProcessor
{
    /**
     * @var RequestValidator
     */
    private $validator;

    /**
     * @var RequestCoercer
     */
    private $coercer;

    /**
     * RequestProcessor constructor.
     *
     * @param RequestValidator $validator
     * @param RequestCoercer   $coercer
     */
    public function __construct(RequestValidator $validator, RequestCoercer $coercer)
    {
        $this->validator = $validator;
        $this->coercer = $coercer;
    }

    /**
     * @param Request         $request
     * @param OperationObject $operationObject
     *
     * @throws InvalidParametersException
     * @throws MalformedContentException
     */
    public function process(Request $request, OperationObject $operationObject)
    {
        $this->coercer->coerceRequest($request, $operationObject);
        $this->validator->setOperationObject($operationObject);
        $this->validator->validateRequest($request);
    }
}
