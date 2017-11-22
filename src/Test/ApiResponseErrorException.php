<?php declare(strict_types=1);
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
     * @var \stdClass
     */
    private $data;

    /**
     * @var string
     */
    private $content;

    /**
     * @param string         $content
     * @param \stdClass|null $data
     * @param int            $httpStatusCode
     */
    public function __construct(string $content, $data, int $httpStatusCode)
    {
        $this->message = "Returned $httpStatusCode";
        if ($data) {
            $this->message = $data->message;
            if (isset($data->logref)) {
                $this->message = "$data->message [logref $data->logref]";
            }
            if (isset($data->errors)) {
                $this->message .= "\n";
                foreach ($data->errors as $attribute => $error) {
                    $this->message .= "[$attribute]: $error\n";
                }
            }
        }

        $this->code    = $httpStatusCode;
        $this->data    = $data;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return \stdClass
     */
    public function getData(): \stdClass
    {
        return $this->data;
    }
}
