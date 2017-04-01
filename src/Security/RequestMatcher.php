<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Security;

use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestMeta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;


/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestMatcher implements RequestMatcherInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function matches(Request $request)
    {
        if(!$request->attributes->has(RequestMeta::ATTRIBUTE_URI)){
            return false;
        }
        $description = $this->repository->get($request->attributes->get(RequestMeta::ATTRIBUTE_URI));
        $operation   = $description
            ->getPath($request->attributes->get(RequestMeta::ATTRIBUTE_PATH))
            ->getOperation($request->getMethod());

        return $operation->isSecured();
    }
}
