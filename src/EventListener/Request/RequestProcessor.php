<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Request;

use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\ScalarSchema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Request\RequestParameterAssembler;
use KleijnWeb\PhpApi\Hydrator\DateTimeSerializer;
use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestProcessor
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var SchemaValidator
     */
    private $validator;

    /**
     * @var ObjectHydrator
     */
    private $hydrator;

    /**
     * @var RequestParameterAssembler
     */
    private $parametersAssembler;

    /**
     * @var DateTimeSerializer
     */
    private $dateTimeSerializer;

    /**
     * RequestProcessor constructor.
     *
     * @param Repository                $repository
     * @param SchemaValidator           $validator
     * @param RequestParameterAssembler $parametersAssembler
     * @param ObjectHydrator            $hydrator
     * @param DateTimeSerializer        $dateTimeSerializer
     */
    public function __construct(
        Repository $repository,
        SchemaValidator $validator,
        RequestParameterAssembler $parametersAssembler,
        ObjectHydrator $hydrator = null,
        DateTimeSerializer $dateTimeSerializer = null
    ) {
        $this->repository          = $repository;
        $this->validator           = $validator;
        $this->hydrator            = $hydrator;
        $this->parametersAssembler = $parametersAssembler;
        $this->dateTimeSerializer  = $dateTimeSerializer ?: new DateTimeSerializer();
    }


    /**
     * @param Request $request
     *
     * @throws ValidationException
     * @throws MalformedContentException
     */
    public function process(Request $request)
    {
        if (!$requestMeta = RequestMeta::fromRequest($request, $this->repository)) {
            throw new \UnexpectedValueException("Not a SwaggerBundle request");
        }

        $operation = $requestMeta->getOperation();

        $body = null;
        if ($request->getContent()) {
            $body = json_decode($request->getContent());
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new MalformedContentException(json_last_error_msg());
            }
        }

        $coercedParams = $this->parametersAssembler->assemble(
            $operation,
            $request->query->all(),
            $request->attributes->all(),
            $request->headers->all(),
            $body
        );

        $result = $this->validator->validate(
            $operation->getRequestSchema(),
            !$operation->hasParameters() && !count((array)$coercedParams) ? null : $coercedParams
        );

        if (!$result->isValid()) {
            throw new ValidationException($result->getErrorMessages());
        }

        foreach ($coercedParams as $attribute => $value) {
            /** @var ScalarSchema $schema */
            if (($schema = $operation->getParameter($attribute)->getSchema()) instanceof ScalarSchema) {
                if ($schema->isDateTime()) {
                    $value = $this->dateTimeSerializer->deserialize($value, $schema);
                }
            }

            $request->attributes->set($attribute, $value);
        }
        if ($this->hydrator
            && $bodyParam = $requestMeta
                ->getDescription()
                ->getRequestBodyParameter($operation->getPath(), $operation->getMethod())
        ) {
            $body = $this->hydrator->hydrate($body, $bodyParam->getSchema());
            $request->attributes->set($bodyParam->getName(), $body);
        }
    }
}
