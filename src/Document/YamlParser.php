<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use Symfony\Component\Yaml\Parser;

/**
 * Facade/Adapter for Symfony\Yaml
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
     * @return mixed
     */
    public function parse($string)
    {
        // Hashmap support is broken, so disable it and attempt fix afterwards
        $data = $this->parser->parse($string, true, false, false);

        return $this->fixHashMaps($data);
    }

    /**
     * @see https://github.com/symfony/symfony/pull/17711
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function fixHashMaps(&$data)
    {
        if (is_array($data)) {
            $shouldBeObject = false;
            $object = new \stdClass();
            $index = 0;
            foreach ($data as $key => &$value) {
                $object->$key = $this->fixHashMaps($value);
                if ($index++ !== $key) {
                    $shouldBeObject = true;
                }
            }
            if ($shouldBeObject) {
                $data = $object;
            }
        }

        return $data;
    }
}
