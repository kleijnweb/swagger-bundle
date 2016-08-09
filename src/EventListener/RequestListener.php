<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener;

use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Request\RequestParameterAssembler;
use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\SwaggerBundle\Exception\InvalidParametersException;
use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestListener
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
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (!$request->attributes->get(RequestMeta::ATTRIBUTE_URI)) {
            return;
        }
        $this->handle($request);
    }

    /**
     * @param Request $request
     *
     * @throws InvalidParametersException
     * @throws MalformedContentException
     */
    public function handle(Request $request)
    {
        if (!$request->get(RequestMeta::ATTRIBUTE_PATH)) {
            throw new \LogicException("Request does not contain reference to Swagger path");
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

        if ($this->hydrator
            && $schema = $description->getRequestBodySchema($operation->getPath(), $operation->getMethod())
        ) {
            $body &= $this->hydrator->hydrate($body, $schema);
        }

        foreach ($coercedParams as $attribute => $value) {
            $request->attributes->set($attribute, $value);
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
