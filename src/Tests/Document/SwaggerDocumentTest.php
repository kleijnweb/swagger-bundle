<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwaggerDocument
     */
    private static $petStoreDocument;

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
    public function willLoadDefinitionIntoArray()
    {
        $this->assertInternalType('array', $this->getPetStoreDocument()->getDefinition());
    }

    /**
     * @test
     */
    public function canGetPathDefinitions()
    {
        $actual = $this->getPetStoreDocument()->getPathDefinitions();
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
        $actual = $this->getPetStoreDocument()->getOperationDefinition('/v2/store/inventory', 'get');
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
        $this->getPetStoreDocument()->getOperationDefinition('/v2/store/inventory', 'GET');
    }


    /**
     * @expectedException \InvalidArgumentException
     * @test
     */
    public function getOperationDefinitionWillFailOnUnknownHttpMethod()
    {
        $this->getPetStoreDocument()->getOperationDefinition('/v2/store/inventory', 'post');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @test
     */
    public function getOperationDefinitionWillFailOnUnknownPath()
    {
        $this->getPetStoreDocument()->getOperationDefinition('/this/is/total/bogus', 'post');
    }

    /**
     * @return SwaggerDocument
     */
    private function getPetStoreDocument()
    {
        if (!self::$petStoreDocument) {
            self::$petStoreDocument = new SwaggerDocument('src/Tests/Functional/PetStore/app/petstore.yml');
        }

        return self::$petStoreDocument;
    }
}
