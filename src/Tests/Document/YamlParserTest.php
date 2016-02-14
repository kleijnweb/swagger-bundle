<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\YamlParser;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class YamlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check Symfony\Yaml bug
     *
     * @see https://github.com/symfony/symfony/issues/17709
     *
     * @test
     */
    public function canParseNumericMap()
    {
        $yaml = <<<YAML
map:
  1: one
  2: two
YAML;
        $parser = new  YamlParser();
        $actual = $parser->parse($yaml);
        $this->assertInternalType('object', $actual);
        $this->assertInternalType('object', $actual->map);
        $this->assertTrue(property_exists($actual->map, '1'));
        $this->assertTrue(property_exists($actual->map, '2'));
        $this->assertSame('one', $actual->map->{'1'});
        $this->assertSame('two', $actual->map->{'2'});
    }

    /**
     * Check Symfony\Yaml bug
     *
     * @see https://github.com/symfony/symfony/pull/17711
     *
     * @test
     */
    public function willParseArrayAsArrayAndObjectAsObject()
    {
        $yaml = <<<YAML
array:
  - key: one
  - key: two
YAML;

        $parser = new  YamlParser();
        $actual = $parser->parse($yaml);
        $this->assertInternalType('object', $actual);

        $this->assertInternalType('array', $actual->array);
        $this->assertInternalType('object', $actual->array[0]);
        $this->assertInternalType('object', $actual->array[1]);
        $this->assertSame('one', $actual->array[0]->key);
        $this->assertSame('two', $actual->array[1]->key);
    }
}
