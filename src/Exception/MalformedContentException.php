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
class MalformedContentException extends \Exception
{
    public function __construct($message = "", $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
