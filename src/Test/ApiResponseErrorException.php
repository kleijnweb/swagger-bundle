<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiResponseErrorException extends \Exception
{
    /**
     * @var object
     */
    private $data;

    /**
     * @var string
     */
    private $json;

    /**
     * @param string $json
     * @param object $data
     * @param int    $httpStatusCode
     */
    public function __construct($json, $data, $httpStatusCode)
    {
        $this->message = "Returned $httpStatusCode";
        if ($data) {
            $this->message = $data->message;
            if (isset($data->logref)) {
                $this->message = "$data->message [logref $data->logref]";
            }

        }

        $this->code = $httpStatusCode;
        $this->data = $data;
        $this->json = $json;
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }
}
