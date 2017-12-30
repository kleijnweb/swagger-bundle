<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Hydrator\ProcessorBuilder;

/**
 * Wrapper around ProcessorBuilder for compatibility
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ObjectHydrator
{
    /**
     * @var ProcessorBuilder
     */
    private $builder;

    /**
     * ObjectHydrator constructor.
     * @param ProcessorBuilder $builder
     */
    public function __construct(ProcessorBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param mixed  $value
     * @param Schema $schema
     * @return mixed
     */
    public function hydrate($value, Schema $schema)
    {
        return $this->builder->build($schema)->hydrate($value);
    }

    /**
     * @param mixed  $value
     * @param Schema $schema
     * @return mixed
     */
    public function dehydrate($value, Schema $schema)
    {
        return $this->builder->build($schema)->dehydrate($value);
    }
}
