<?php
declare(strict_types = 1);
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
     * InvalidParametersException constructor.
     *
     * @param string          $message
     * @param array           $validationErrors
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(string $message, array $validationErrors, int $code = 400, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->validationErrors = $validationErrors;
    }

    /**
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
