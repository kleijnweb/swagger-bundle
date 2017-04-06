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
}
