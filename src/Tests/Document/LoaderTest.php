<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotReadableException;
use KleijnWeb\SwaggerBundle\Document\Loader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willFailWhenFileDoesNotExist()
    {
        try {
            $loader = new Loader();
            $loader->load('does/not/exist.json');
        } catch (ResourceNotReadableException $e) {
            return;
        }
        $this->fail("Expected ResourceNotReadableException");

    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotDecodableException
     */
    public function willFailWhenYamlIsNotDecodable()
    {
        $yaml = <<<YAML
foo:
  bar: 1
 foo:
  bar: 1
YAML;

        $this->loadContentViaVfs('invalid.yaml', $yaml);
    }

    /**
     * @test
     */
    public function canLoadValidYaml()
    {
        $yaml = <<<YAML
foo:
  bar: 1
YAML;
        $this->assertSame(1, $this->loadContentViaVfs('invalid.yaml', $yaml)->foo->bar);
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotDecodableException
     */
    public function willFailWhenJsonIsNotDecodable()
    {
        $this->loadContentViaVfs('invalid/json.json', 'NOT VALID JSON');
    }

    /**
     * @test
     */
    public function canLoadValidJson()
    {
        $this->loadContentViaVfs('some/valid.json', json_encode(['valid' => true]));
    }

    /**
     * @test
     * @expectedException \KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotDecodableException
     */
    public function willFailWhenContentIsNeitherYamlNorJson()
    {
        $this->loadContentViaVfs('resource.xml', '<foo>bar</foo>');
    }

    /**
     * @param string $path
     * @param string $content
     *
     * @return \stdClass
     * @throws \KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotDecodableException
     * @throws \KleijnWeb\SwaggerBundle\Document\Exception\ResourceNotReadableException
     * @throws \org\bovigo\vfs\vfsStreamException
     */
    private function loadContentViaVfs($path, $content)
    {
        $rootDirName = 'willProperlyResolveExternalReferences';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(vfsStream::newDirectory($rootDirName));

        $loader = new Loader();

        $filePathName = vfsStream::url("$rootDirName/$path");

        $dir = dirname($filePathName);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filePathName, $content);

        return $loader->load($filePathName);
    }
}
