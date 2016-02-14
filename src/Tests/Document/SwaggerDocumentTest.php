<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canGetPathDefinitions()
    {
        $actual = self::getPetStoreDocument()->getPathDefinitions();
        $this->assertInternalType('object', $actual);

        // Check a few attributes
        $this->assertObjectHasAttribute('/pet', $actual);
        $this->assertObjectHasAttribute('/pet/findByStatus', $actual);
        $this->assertObjectHasAttribute('/store/inventory', $actual);
        $this->assertObjectHasAttribute('/user', $actual);
    }

    /**
     * @test
     */
    public function getOperationDefinition()
    {
        $actual = self::getPetStoreDocument()->getOperationDefinition('/store/inventory', 'get');
        $this->assertInternalType('object', $actual);

        // Check a few attributes
        $this->assertObjectHasAttribute('parameters', $actual);
        $this->assertObjectHasAttribute('responses', $actual);
        $this->assertObjectHasAttribute('security', $actual);
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
     * @return SwaggerDocument
     */
    public static function getPetStoreDocument()
    {
        $repository = new DocumentRepository('src/Tests/Functional/PetStore');

        return $repository->get('app/swagger/petstore.yml');
    }
}
