<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\Generator;

use KleijnWeb\SwaggerBundle\Tests\Dev\Document\SwaggerDocumentTest;
use KleijnWeb\SwaggerBundle\Dev\Generator\ResourceGenerator;
use KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\PetStoreBundle;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResourceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canRenderResourcesFromPetStore()
    {
        $bundle = new PetStoreBundle();
        $document = SwaggerDocumentTest::getPetStoreDocument();
        $document->resolveReferences();
        $generator = new ResourceGenerator();
        $generator->setSkeletonDirs('src/Dev/Resources/skeleton');
        $generator->generate($bundle, $document, 'Foo\Bar');
        $files = [
            'User.php',
            'Category.php',
            'Pet.php',
            'Order.php',
        ];

        foreach ($files as $file) {
            $filePathName = $bundle->getPath() . '/Foo/Bar/' . $file;
            $this->assertTrue(
                file_exists($filePathName),
                sprintf('%s has not been generated', $filePathName)
            );
            $content = file_get_contents($filePathName);
            $this->assertContains("namespace {$bundle->getNamespace()}\\Foo\\Bar;", $content);
        }
    }
}
