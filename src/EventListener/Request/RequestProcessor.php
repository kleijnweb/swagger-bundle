<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Request;

use KleijnWeb\PhpApi\Descriptions\Description\Parameter;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Request\RequestParameterAssembler;
use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
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
     * RequestProcessor constructor.
     *
     * @param Repository                $repository
     * @param SchemaValidator           $validator
     * @param RequestParameterAssembler $parametersAssembler
     * @param ObjectHydrator            $hydrator
     */
    public function __construct(
        Repository $repository,
        SchemaValidator $validator,
        RequestParameterAssembler $parametersAssembler,
        ObjectHydrator $hydrator = null
    ) {
        $this->repository          = $repository;
        $this->validator           = $validator;
        $this->hydrator            = $hydrator;
        $this->parametersAssembler = $parametersAssembler;
    }


    /**
     * @param Request $request
     *
     * @throws InvalidParametersException
     * @throws MalformedContentException
     */
    public function process(Request $request)
    {
        if (!$request->attributes->has(RequestMeta::ATTRIBUTE_URI)) {
            throw  new \UnexpectedValueException("Missing document URI");
        }
        $description = $this->repository->get($request->attributes->get(RequestMeta::ATTRIBUTE_URI));
        $operation   = $description
            ->getPath($request->attributes->get(RequestMeta::ATTRIBUTE_PATH))
            ->getOperation(
                $request->getMethod()
            );

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

        foreach ($coercedParams as $attribute => $value) {
            $request->attributes->set($attribute, $value);
        }

        if ($this->hydrator
            && $bodyParam = $description->getRequestBodyParameter($operation->getPath(), $operation->getMethod())
        ) {
            $body = $this->hydrator->hydrate($body, $bodyParam->getSchema());
            $request->attributes->set($bodyParam->getName(), $body);
        }

        $result = $this->validator->validate($operation->getRequestSchema(), $request->attributes->all());

        $request->attributes->set(
            RequestMeta::ATTRIBUTE,
            new RequestMeta($description, $operation)
        );

        if (!$result->isValid()) {
            throw new InvalidParametersException($result->getErrorMessages());
        }
    }
}
