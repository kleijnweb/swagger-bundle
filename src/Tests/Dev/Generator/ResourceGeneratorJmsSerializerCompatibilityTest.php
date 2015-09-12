<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\Generator;

use JMS\Serializer\SerializerBuilder;
use KleijnWeb\SwaggerBundle\Tests\Dev\Document\SwaggerDocumentTest;
use KleijnWeb\SwaggerBundle\Dev\Generator\ResourceGenerator;
use KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\PetStoreBundle;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResourceGeneratorJmsSerializerCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $bundle = new PetStoreBundle();
        $document = SwaggerDocumentTest::getPetStoreDocument();
        $document->resolveReferences();
        $generator = new ResourceGenerator();
        $generator->setSkeletonDirs('src/Dev/Resources/skeleton');
        $generator->generate($bundle, $document, 'Model\Jms');

        require_once $bundle->getPath() . '/Model/Jms/Pet.php';
        require_once $bundle->getPath() . '/Model/Jms/Tag.php';
        require_once $bundle->getPath() . '/Model/Jms/Category.php';
    }

    /**
     * @test
     */
    public function canSerializeAPet()
    {
        $pet = new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Pet();
        $pet
            ->setId(1234567)
            ->setName('doggie')
            ->setPhotourls(['/a/b/c', '/d/e/f'])
            ->setTags([
                (new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Tag())->setName('purebreeds'),
                (new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Tag())->setName('puppies')
            ])
            ->setCategory(
                (new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Category())->setName('Dogs')
            );

        $serializer = SerializerBuilder::create()->build();
        $actual = json_decode($serializer->serialize($pet, 'json'), true);
        $expected = [
            'id'         => 1234567,
            'category'   => ['name' => 'Dogs'],
            'name'       => 'doggie',
            'photo_urls' => ['/a/b/c', '/d/e/f'],
            'tags'       => [
                ['name' => 'purebreeds'],
                ['name' => 'puppies'],
            ]

        ];
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function canDeserializeAPet()
    {
        $data = [
            'id'         => 1234567,
            'category'   => ['name' => 'Dogs'],
            'name'       => 'doggie',
            'photo_urls' => ['/a/b/c', '/d/e/f'],
            'tags'       => [
                ['name' => 'purebreeds'],
                ['name' => 'puppies'],
            ]
        ];

        $serializer = SerializerBuilder::create()->build();
        $actual = $serializer->deserialize(
            json_encode($data),
            'KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Pet',
            'json'
        );

        $expected = new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Pet();
        $expected
            ->setId(1234567)
            ->setName('doggie')
            ->setPhotourls(['/a/b/c', '/d/e/f'])
            ->setTags([
                (new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Tag())->setName('purebreeds'),
                (new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Tag())->setName('puppies')
            ])
            ->setCategory(
                (new \KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Model\Jms\Category())->setName('Dogs')
            );

        $this->assertEquals($expected, $actual);
    }
}
