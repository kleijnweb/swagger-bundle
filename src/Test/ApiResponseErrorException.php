<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

class ApiResponseErrorException extends \Exception
{
    /**
     * @param object $json
     * @param int    $httpStatusCode
     */
    public function __construct($json, $httpStatusCode)
    {
        $this->message = "$json->message [logref $json->logref]";
        $this->code = $httpStatusCode;
    }
}
