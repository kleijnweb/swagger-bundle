<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Response;

use Ramsey\VndError\VndError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class VndErrorResponse extends JsonResponse
{
    const DEFAULT_STATUS = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * @param VndError $vndError
     * @param int      $status
     * @param array    $headers
     */
    public function __construct(VndError $vndError, $status = self::DEFAULT_STATUS, array $headers = [])
    {
        $headers = array_merge(['Content-Type' => 'application/vnd.error+json'], $headers);
        parent::__construct($vndError->asJson(false, false), $status, $headers);
    }
}
