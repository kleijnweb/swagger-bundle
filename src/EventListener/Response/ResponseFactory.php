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
use KleijnWeb\PhpApi\Middleware\Util\OkStatusResolver;
use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
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
     * @var OkStatusResolver
     */
    private $okStatusResolver;

    /**
     * @param ObjectHydrator   $hydrator
     * @param SchemaValidator  $validator
     * @param OkStatusResolver $okStatusResolver
     */
    public function __construct(
        ObjectHydrator $hydrator = null,
        SchemaValidator $validator = null,
        OkStatusResolver $okStatusResolver = null
    ) {
        $this->hydrator         = $hydrator;
        $this->validator        = $validator;
        $this->okStatusResolver = $okStatusResolver ?: new OkStatusResolver();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Request $request
     * @param mixed   $operationResult
     *
     * @return Response
     * @throws ValidationException
     */
    public function createResponse(Request $request, $operationResult = null)
    {
        /** @var RequestMeta $meta */
        $meta = $request->attributes->get(RequestMeta::ATTRIBUTE);

        $statusCode = $this->okStatusResolver->resolve($operationResult, $meta->getOperation());

        $schema = $meta->getOperation()->getResponse($statusCode)->getSchema();

        if ($this->hydrator && $operationResult !== null) {
            $operationResult = $this->hydrator->dehydrate($operationResult, $schema);
        }

        $operationResult = $operationResult === null ? '' : $operationResult;

        if ($this->validator) {
            $validationResult = $this->validator->validate($schema, $operationResult);

            if (!$validationResult->isValid()) {
                throw new ValidationException(
                    $validationResult->getErrorMessages(),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    ValidationException::MESSAGE_OUTPUT
                );
            };
        }

        return new JsonResponse($operationResult, $statusCode);
    }
}
