<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * Facade for Symfony\Yaml
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class YamlParser
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * Construct the wrapper
     */
    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * @param string $string
     *
     * @return object
     */
    public function parse($string)
    {
        return $this->parser->parse($string, true, false, true);
    }
}
