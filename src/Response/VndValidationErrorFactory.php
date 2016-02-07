<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response;

use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use Ramsey\VndError\VndError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndValidationErrorFactory
{
    const DEFAULT_MESSAGE = 'Input Validation Failure';


    /**
     * @param Request                    $request
     * @param InvalidParametersException $exception
     *
     * @param string|null                $logRef
     *
     * @return VndError
     */
    public function create(Request $request, InvalidParametersException $exception, $logRef)
    {
        $vndError = new VndError(self::DEFAULT_MESSAGE, $logRef);
        $vndError->addLink('help', $request->attributes->get('_resource'), ['title' => 'Error Information']);
        $vndError->addLink('about', $request->getUri(), ['title' => 'Error Information']);
        var_dump($exception->getValidationErrors());

        return $vndError;
    }
}
