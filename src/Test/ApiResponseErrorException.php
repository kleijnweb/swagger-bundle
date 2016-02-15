<?php
declare(strict_types = 1);
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
     * @param string    $json
     * @param \stdClass $data
     * @param int       $httpStatusCode
     */
    public function __construct(string $json, \stdClass $data, int $httpStatusCode)
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
    public function getJson(): string
    {
        return $this->json;
    }

    /**
     * @return \stdClass
     */
    public function getData():\stdClass
    {
        return $this->data;
    }
}
