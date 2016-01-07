<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\Document;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willFailWhenPathDoesNotExist()
    {
        new SwaggerDocument('/this/is/total/bogus');
    }

    /**
     * @test
     */
    public function willLoadDefinitionIntoArrayObject()
    {
        $this->assertInstanceOf('ArrayObject', self::getPetStoreDocument()->getDefinition());
    }

    /**
     * @test
     */
    public function canGetPathDefinitions()
    {
        $actual = self::getPetStoreDocument()->getPathDefinitions();
        $this->assertInternalType('array', $actual);

        // Check a few keys
        $this->assertArrayHasKey('/pet', $actual);
        $this->assertArrayHasKey('/pet/findByStatus', $actual);
        $this->assertArrayHasKey('/store/inventory', $actual);
        $this->assertArrayHasKey('/user', $actual);
    }

    /**
     * @test
     */
    public function getOperationDefinition()
    {
        $actual = self::getPetStoreDocument()->getOperationDefinition('/store/inventory', 'get');
        $this->assertInternalType('array', $actual);

        // Check a few keys
        $this->assertArrayHasKey('parameters', $actual);
        $this->assertArrayHasKey('responses', $actual);
        $this->assertArrayHasKey('security', $actual);
    }

    /**
     * @test
     */
    public function getOperationDefinitionHttpMethodIsCaseInsensitive()
    {
        self::getPetStoreDocument()->getOperationDefinition('/store/inventory', 'GET');
    }


    /**
     * @expectedException \InvalidArgumentException
     * @test
     */
    public function getOperationDefinitionWillFailOnUnknownHttpMethod()
    {
        self::getPetStoreDocument()->getOperationDefinition('/store/inventory', 'post');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @test
     */
    public function getOperationDefinitionWillFailOnUnknownPath()
    {
        self::getPetStoreDocument()->getOperationDefinition('/this/is/total/bogus', 'post');
    }

    /**
     * @test
     */
    public function canWriteValidYamlToFileSystem()
    {
        $originalHash = md5_file('src/Tests/Functional/PetStore/app/swagger/petstore.yml');

        $document = self::getPetStoreDocument();
        $document->write();

        $newHash = md5_file('src/Tests/Functional/PetStore/app/swagger/petstore.yml');

        $this->assertSame($originalHash, $newHash);
    }

    /**
     * @test
     */
    public function gettingArrayCopyWillLeaveEmptyArraysAsEmptyArrays()
    {
        $document = self::getPetStoreDocument();
        $data = $document->getArrayCopy();

        $emptyParameters = $data['paths']['/store/inventory']['get']['parameters'];
        $emptyAuthSpec = $data['paths']['/store/inventory']['get']['security'][0]['api_key'];

        $this->assertSame([], $emptyParameters);
        $this->assertSame([], $emptyAuthSpec);
    }

    /**
     * @test
     */
    public function canWriteModifiedYamlToFileSystem()
    {
        $originalHash = md5_file('src/Tests/Functional/PetStore/app/swagger/petstore.yml');
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('canWriteModifiedYamlToFileSystem'));

        $modifiedPath = vfsStream::url('canWriteModifiedYamlToFileSystem/modified.yml');

        $document = self::getPetStoreDocument();
        $definition = $document->getDefinition();
        $definition->version = '0.0.2';
        $document->write($modifiedPath);

        $newHash = md5_file($modifiedPath);

        $this->assertNotSame($originalHash, $newHash);
    }

    /**
     * @test
     */
    public function canModifiedYamlWrittenToFileSystemHandlesEmptyArraysCorrectly()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(
            new vfsStreamDirectory('canModifiedYamlWrittenToFileSystemHandlesEmptyArraysCorrectly')
        );

        $modifiedPath = vfsStream::url('canModifiedYamlWrittenToFileSystemHandlesEmptyArraysCorrectly/modified.yml');

        $document = self::getPetStoreDocument();
        $definition = $document->getDefinition();
        $definition->version = '0.0.2';
        $document->write($modifiedPath);

        $content = file_get_contents($modifiedPath);
        $this->assertNotRegExp('/\: \{  \}/', $content);
    }

    /**
     * @test
     */
    public function canResolveResourceSchemaReferences()
    {
        $document = self::getPetStoreDocument();
        $schemas = $document->getResourceSchemas();
        $propertySchema = $schemas['Pet']['properties']['category'];
        $this->assertArrayNotHasKey('$ref', $propertySchema);
        $this->assertArrayHasKey('id', $propertySchema);
        $this->assertSame('object', $propertySchema['type']);
    }

    /**
     * @test
     */
    public function canResolveParameterSchemaReferences()
    {
        $document = new SwaggerDocument('src/Tests/Functional/PetStore/app/swagger/instagram.yml');
        $pathDefinitions = $document->getPathDefinitions();
        $argumentPseudoSchema = $pathDefinitions['/users/{user-id}']['parameters'][0];
        $this->assertArrayNotHasKey('$ref', $argumentPseudoSchema);
        $this->assertArrayHasKey('in', $argumentPseudoSchema);
        $this->assertSame('user-id', $argumentPseudoSchema['name']);
    }

    /**
     * @return SwaggerDocument
     */
    public static function getPetStoreDocument()
    {
        return new SwaggerDocument('src/Tests/Functional/PetStore/app/swagger/petstore.yml');
    }
}
