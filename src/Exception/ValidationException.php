<?php declare(strict_types = 1);
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
class ValidationException extends \Exception
{
    const MESSAGE_INPUT = 'Input validation failed';
    const MESSAGE_OUTPUT = 'Output validation failed';

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
    public function __construct(
        array $validationErrors,
        int $code = 400,
        string $message = self::MESSAGE_INPUT,
        \Exception $previous = null
    ) {
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
