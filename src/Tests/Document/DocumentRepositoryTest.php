<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DocumentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    protected function setUp()
    {
        $this->repository = new DocumentRepository();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willFailWhenKeyIsEmpty()
    {
        $this->repository->get('');
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotReadableException
     */
    public function willFailWhenPathDoesNotExist()
    {
        $this->repository->get('/this/is/total/bogus');
    }

    /**
     * @test
     */
    public function gettingDocumentThatDoestExistWillConstructIt()
    {
        $document = $this->repository->get('src/Tests/Functional/PetStore/app/swagger/petstore.yml');
        $this->assertInstanceOf('KleijnWeb\SwaggerBundle\Document\SwaggerDocument', $document);
    }

    /**
     * @test
     */
    public function definitionIsObject()
    {
        $document = $this->repository->get('src/Tests/Functional/PetStore/app/swagger/petstore.yml');
        $this->assertInternalType('object', $document->getDefinition());
    }

    /**
     * @test
     */
    public function willCache()
    {
        $path = 'src/Tests/Functional/PetStore/app/swagger/petstore.yml';
        $cache = $this->getMockBuilder('Doctrine\Common\Cache\ArrayCache')->disableOriginalConstructor()->getMock();
        $repository = new DocumentRepository(null, $cache);
        $cache->expects($this->exactly(1))->method('fetch')->with($path);
        $cache->expects($this->exactly(1))->method('save')->with($path, $this->isType('object'));
        $document = $repository->get($path);
        $this->assertInternalType('object', $document->getDefinition());
    }

    /**
     * @test
     */
    public function canUsePathPrefix()
    {
        $this->repository = new DocumentRepository('src/Tests/Functional/PetStore');
        $document = $this->repository->get('app/swagger/petstore.yml');
        $this->assertInstanceOf('KleijnWeb\SwaggerBundle\Document\SwaggerDocument', $document);
    }
}
