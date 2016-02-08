<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Exception;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class InvalidParametersException extends \Exception
{
    /**
     * @var array
     */
    private $validationErrors;

    /**
     * @param string     $message
     * @param int        $code
     * @param array      $validationErrors
     * @param \Exception $previous
     */
    public function __construct($message, array $validationErrors, $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->validationErrors = $validationErrors;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}
