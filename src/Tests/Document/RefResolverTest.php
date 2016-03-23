<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\Loader;
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

        $schemas        = $resolver->getDefinition()->definitions;
        $propertySchema = $schemas->Pet->properties->category;

        $this->assertObjectNotHasAttribute('$ref', $propertySchema);
        $this->assertObjectHasAttribute('x-ref-id', $propertySchema);
        $this->assertSame('object', $propertySchema->type);
    }

    /**
     * @test
     */
    public function canResolveParameterSchemaReferences()
    {
        $resolver        = $this->construct('instagram.yml');
        $pathDefinitions = $resolver->getDefinition()->paths;
        $pathDefinition  = $pathDefinitions->{'/users/{user-id}'};

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
     */
    public function canResolveReferencesWithSlashed()
    {
        $resolver = $this->construct('partials/slashes.yml');
        $this->assertSame('thevalue', $resolver->resolve()->Foo->bar);
    }

    /**
     * @test
     *
     */
    public function canResolveExternalReferencesInExample()
    {
        $resolver = $this->construct('composite.yml');
        $document = $resolver->resolve();

        $this->assertObjectHasAttribute('schema', $document->responses->Created);

        $response = $document->paths->{'/pet'}->post->responses->{'500'};

        $this->assertObjectHasAttribute('description', $response);
    }

    /**
     * @test
     */
    public function canUnResolve()
    {
        $resolver = $this->construct('composite.yml');

        $expected = clone $resolver->getDefinition();
        $resolver->resolve();
        $document = $resolver->unresolve();

        $this->assertObjectNotHasAttribute('schema', $document->responses->Created);
        $this->assertEquals($expected, $document);
    }

    /**
     * @dataProvider externalReferenceProvider
     * @test
     *
     * @param mixed           $expected
     * @param string          $fileUrl
     * @param string          $uri
     * @param array|\stdClass $content
     *
     */
    public function willProperlyResolveExternalReferences($expected, $fileUrl, $uri, $content)
    {
        $mockLoader = $this
            ->getMockBuilder('KleijnWeb\SwaggerBundle\Document\Loader')
            ->disableOriginalConstructor()
            ->getMock();

        $object   = (object)['$ref' => $uri];
        $resolver = new RefResolver($object, '/somedir/faux', $mockLoader);

        $mockLoader
            ->expects($this->once())
            ->method('load')
            ->with($fileUrl)
            ->willReturn($content);

        $value = $resolver->resolve();

        $this->assertSame($expected, $value);
    }

    /**
     * @return array
     */
    public static function externalReferenceProvider()
    {
        return [
            [
                'foo',
                '/somedir/entities.json',
                'entities.json#/definitions/SomeType',
                (object)['definitions' => (object)['SomeType' => 'foo']]
            ],
            [
                'bar',
                '/somedir/entities.yaml',
                'entities.yaml#/definitions/SomeType',
                (object)['definitions' => (object)['SomeType' => 'bar']]
            ],
            [
                'mary',
                '/somedir/entities/had/a/little.yaml',
                'entities/had/a/little.yaml#/Lamb',
                (object)['Lamb' => 'mary']
            ],
            [
                'wow',
                'wss://many:external@references.com:8080/so.yml?much',
                'wss://many:external@references.com:8080/so.yml?much#/flexibility',
                (object)['flexibility' => 'wow']
            ],
            [
                'local',
                '/somedir/local.yml',
                'file://local.yml#/definitions/SomeType',
                (object)['definitions' => (object)['SomeType' => 'local']]
            ]
        ];
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
        $parser   = new YamlParser();
        /** @var object $object */
        $object   = $parser->parse($contents);
        $resolver = new RefResolver($object, $filePath);

        return $resolver;
    }
}
