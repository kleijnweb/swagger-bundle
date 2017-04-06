<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\EventListener\Request;

use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestMeta
{
    const ATTRIBUTE      = '_swagger.meta';
    const ATTRIBUTE_URI  = '_swagger.uri';
    const ATTRIBUTE_PATH = '_swagger.path';

    /**
     * @var Description
     */
    private $description;

    /**
     * @var Operation
     */
    private $operation;

    /**
     * RequestMeta constructor.
     *
     * @param Description $description
     * @param Operation   $operation
     */
    public function __construct(Description $description, Operation $operation)
    {
        $this->description = $description;
        $this->operation   = $operation;
    }

    /**
     * @return Operation
     */
    public function getOperation(): Operation
    {
        return $this->operation;
    }

    /**
     * @return Description
     */
    public function getDescription(): Description
    {
        return $this->description;
    }

    /**
     * @param Request    $request
     * @param Repository $repository
     * @return RequestMeta|null
     */
    public static function fromRequest(Request $request, Repository $repository)
    {
        if ($request->attributes->has(RequestMeta::ATTRIBUTE)) {
            return $request->attributes->get(RequestMeta::ATTRIBUTE);
        }

        if (!$request->attributes->has(RequestMeta::ATTRIBUTE_URI)) {
            return null;
        }

        $description = $repository->get($request->attributes->get(RequestMeta::ATTRIBUTE_URI));
        $operation   = $description
            ->getPath($request->attributes->get(RequestMeta::ATTRIBUTE_PATH))
            ->getOperation($request->getMethod());

        $self = new self($description, $operation);
        $request->attributes->set(RequestMeta::ATTRIBUTE, $self);

        return $self;
    }
}
