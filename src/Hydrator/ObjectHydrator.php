<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Hydrator;

use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Hydrator\ProcessorBuilder;
use KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Processor;

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
     * @var Processor
     */
    private $processors = [];

    /**
     * ObjectHydrator constructor.
     *
     * @param ProcessorBuilder $builder
     */
    public function __construct(ProcessorBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param mixed  $value
     * @param Schema $schema
     *
     * @return mixed
     */
    public function hydrate($value, Schema $schema)
    {
        return $this->getProcessor($schema)->hydrate($value);
    }

    /**
     * @param mixed  $value
     * @param Schema $schema
     *
     * @return mixed
     */
    public function dehydrate($value, Schema $schema)
    {
        return $this->getProcessor($schema)->dehydrate($value);
    }

    private function getProcessor(Schema $schema): Processor
    {
        if (!isset($this->processors[spl_object_id($schema)])) {
            $this->processors[spl_object_id($schema)] = $this->builder->build($schema);
        }
        return $this->processors[spl_object_id($schema)];
    }
}
