<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\DocumentFixer;

use KleijnWeb\SwaggerBundle\Dev\DocumentFixer\Fixers\ResourceDefinitionFixer;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResourceDefinitionFixerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willAddVndErrorSchema()
    {
        $this->markTestIncomplete();
        $fixer = new ResourceDefinitionFixer();
        $document = new SwaggerDocument(__DIR__ . '/assets/minimal.yml');
        $fixer->fix($document);

        $definition = $document->getDefinition();
        $this->assertArrayHasKey('definitions', $definition);
        $this->assertArrayHasKey('Pet', $definition['definitions']);
        $this->assertArrayHasKey('type', $definition['definitions']['Pet']);
        $this->assertArrayHasKey('required', $definition['definitions']['Pet']);
        $this->assertArrayHasKey('properties', $definition['definitions']['Pet']);
    }
}
