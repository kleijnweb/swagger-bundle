<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndErrorResponse extends JsonResponse
{
    const DEFAULT_STATUS = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * @param string $message
     * @param int    $status
     * @param null   $logref
     * @param array  $headers
     */
    public function __construct($message, $status = self::DEFAULT_STATUS, $logref = null, $headers = [])
    {
        $data = ['message' => $message];
        if (null !== $logref) {
            $data['logref'] = $logref;
        }
        $headers = array_merge(['Content-Type' => 'application/vnd.error+json'], $headers);
        parent::__construct($data, $status, $headers);

    }
}
