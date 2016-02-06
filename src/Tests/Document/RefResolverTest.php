<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\RefResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RefResolverTest extends \PHPUnit_Framework_TestCase
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
        $actual = Yaml::parse($yaml, true, false, true);
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

        $actual = Yaml::parse($yaml, true, false, true);
        $this->assertInternalType('object', $actual);

        $this->assertInternalType('array', $actual->array);
        $this->assertInternalType('object', $actual->array[0]);
        $this->assertInternalType('object', $actual->array[1]);
        $this->assertSame('one', $actual->array[0]->key);
        $this->assertSame('two', $actual->array[1]->key);
    }

    /**
     * @test
     */
    public function canResolveResourceSchemaReferences()
    {
        $resolver = $this->construct('petstore.yml');
        $resolver->resolve();
        $schemas = $resolver->getDocument()->definitions;
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
        $pathDefinitions = $resolver->getDocument()->paths;
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
        $document = $resolver->getDocument();
        $schema = $document->responses->Created->schema;
        $this->assertObjectHasAttribute('type', $schema);
        $response = $document->paths->{'/pet'}->post->responses->{'500'};
        $this->assertObjectHasAttribute('description', $response);
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
        $object = Yaml::parse($contents, true, false, true);
        $resolver = new RefResolver($object, $filePath);

        return $resolver;
    }
}
