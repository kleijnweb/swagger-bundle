<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Document\Specification;
use KleijnWeb\SwaggerBundle\Document\Specification\Operation;
use KleijnWeb\SwaggerBundle\Serialize\TypeResolver\SerializerTypeDefinitionMap;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RequestMeta
{
    /**
     * @var SerializerTypeDefinitionMap
     */
    private $definitionMap;

    /**
     * @var Specification
     */
    private $specification;

    /**
     * @var Operation
     */
    private $operation;

    /**
     * RequestMeta constructor.
     *
     * @param Specification               $specification
     * @param Operation                   $operation
     * @param SerializerTypeDefinitionMap $definitionMap
     */
    public function __construct(
        Specification $specification,
        Operation $operation,
        SerializerTypeDefinitionMap $definitionMap = null
    ) {
        $this->definitionMap = $definitionMap;
        $this->specification = $specification;
        $this->operation     = $operation;
    }

    /**
     * @return SerializerTypeDefinitionMap|null
     */
    public function getDefinitionMap()
    {
        return $this->definitionMap;
    }

    /**
     * @return Specification
     */
    public function getSpecification(): Specification
    {
        return $this->specification;
    }

    /**
     * @return Operation
     */
    public function getOperation(): Operation
    {
        return $this->operation;
    }
}
