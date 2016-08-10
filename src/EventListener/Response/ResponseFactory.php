<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Response;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestMeta;
use KleijnWeb\SwaggerBundle\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var SchemaValidator
     */
    private $validator;

    /**
     * @param ObjectHydrator  $hydrator
     * @param SchemaValidator $validator
     */
    public function __construct(ObjectHydrator $hydrator = null, SchemaValidator $validator = null)
    {
        $this->hydrator  = $hydrator;
        $this->validator = $validator;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Request $request
     * @param mixed   $data
     *
     * @return Response
     * @throws ValidationException
     */
    public function createResponse(Request $request, $data = null)
    {
        /** @var RequestMeta $meta */
        $meta           = $request->attributes->get(RequestMeta::ATTRIBUTE);
        $operation      = $meta->getOperation();
        $body           = $data === null ? '' : $data;
        $statusCode     = 200;
        $codes          = $operation->getStatusCodes();
        $understands204 = in_array(204, $codes);

        foreach ($codes as $code) {
            if ('2' == substr((string)$code, 0, 1)) {
                $statusCode = $code;
                break;
            }
        }
        $schema = $meta->getOperation()->getResponse($statusCode)->getSchema();

        if ($this->validator) {
            $result = $this->validator->validate($schema, $body);

            if (!$result->isvalid()) {
                throw new ValidationException(
                    $result->getErrorMessages(),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    ValidationException::MESSAGE_OUTPUT
                );
            };
        }

        if ($body !== '') {
            $body = $this->hydrator->dehydrate($body, $schema);
        } elseif ($understands204) {
            $statusCode = 204;
        }

        return new JsonResponse($body, $statusCode);
    }
}
