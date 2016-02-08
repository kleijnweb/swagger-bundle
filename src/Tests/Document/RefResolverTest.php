<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\RefResolver;
use KleijnWeb\SwaggerBundle\Document\YamlParser;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RefResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canResolveResourceSchemaReferences()
    {
        $resolver = $this->construct('petstore.yml');
        $resolver->resolve();
        $schemas = $resolver->getDefinition()->definitions;
        $propertySchema = $schemas->Pet->properties->category;
        $this->assertObjectNotHasAttribute('$ref', $propertySchema);
        $this->assertObjectHasAttribute('id', $propertySchema);
        $this->assertSame('object', $propertySchema->type);
    }

    /**
     * @test
     */
    public function canResolveParameterSchemaReferences()
    {
        $resolver = $this->construct('instagram.yml');
        $pathDefinitions = $resolver->getDefinition()->paths;
        $pathDefinition = $pathDefinitions->{'/users/{user-id}'};
        $this->assertInternalType('array', $pathDefinition->parameters);
        $pathDefinition = $pathDefinitions->{'/users/{user-id}'};
        $resolver->resolve();
        $this->assertInternalType('array', $pathDefinition->parameters);
        $argumentPseudoSchema = $pathDefinition->parameters[0];
        $this->assertObjectNotHasAttribute('$ref', $argumentPseudoSchema);
        $this->assertObjectHasAttribute('in', $argumentPseudoSchema);
        $this->assertSame('user-id', $argumentPseudoSchema->name);
    }

    /**
     * @test
     *
     */
    public function canResolveExternalReferences()
    {
        $resolver = $this->construct('composite.yml');
        $resolver->resolve();
        $document = $resolver->getDefinition();
        $schema = $document->responses->Created->schema;
        $this->assertObjectHasAttribute('type', $schema);
        $response = $document->paths->{'/pet'}->post->responses->{'500'};
        $this->assertObjectHasAttribute('description', $response);
    }

    /**
     * @test
     */
    public function canUnResolve()
    {
        $this->markTestIncomplete();
    }

    /**
     * @param string $path
     *
     * @return RefResolver
     */
    private function construct($path)
    {
        $filePath = "src/Tests/Functional/PetStore/app/swagger/$path";
        $contents = file_get_contents($filePath);
        $parser = new YamlParser();
        $object = $parser->parse($contents);
        $resolver = new RefResolver($object, $filePath);

        return $resolver;
    }
}
