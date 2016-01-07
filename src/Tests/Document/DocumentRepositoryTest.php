<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\Document;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DocumentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willFailWhenKeyIsEmpty()
    {
        $repository = new DocumentRepository();
        $repository->get('');
    }

    /**
     * @test
     */
    public function gettingDocumentThatDoestExistWillConstructIt()
    {
        $repository = new DocumentRepository();
        $document = $repository->get('src/Tests/Functional/PetStore/app/swagger/petstore.yml');
        $this->assertInstanceOf('KleijnWeb\SwaggerBundle\Document\SwaggerDocument', $document);
    }

    /**
     * @test
     */
    public function canUsePathPrefix()
    {
        $repository = new DocumentRepository('src/Tests/Functional/PetStore');
        $document = $repository->get('app/swagger/petstore.yml');
        $this->assertInstanceOf('KleijnWeb\SwaggerBundle\Document\SwaggerDocument', $document);
    }
}
