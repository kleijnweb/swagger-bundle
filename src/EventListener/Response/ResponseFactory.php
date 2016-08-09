<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Response;

use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\SwaggerBundle\EventListener\RequestMeta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactory
{
    /**
     * @var ObjectHydrator
     */
    private $hydrator;

    /**
     * @param ObjectHydrator $hydrator
     */
    public function __construct(ObjectHydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Request $request
     * @param mixed   $data
     *
     * @return Response
     */
    public function createResponse(Request $request, $data = null)
    {
        /** @var RequestMeta $meta */
        $meta      = $request->attributes->get(RequestMeta::ATTRIBUTE);
        $operation = $meta->getOperation();

        $statusCode     = 200;
        $codes          = $operation->getStatusCodes();
        $understands204 = in_array(204, $codes);

        foreach ($codes as $code) {
            if ('2' == substr((string)$code, 0, 1)) {
                $statusCode = $code;
                break;
            }
        }

        if ($data !== null) {
            $data = $this->hydrator->hydrate(
                $data,
                $meta->getOperation()->getResponse($statusCode)->getSchema()
            );
        } elseif ($understands204) {
            $statusCode = 204;
        }

        return new Response($data, $statusCode, ['Content-Type' => 'application/json']);
    }
}
