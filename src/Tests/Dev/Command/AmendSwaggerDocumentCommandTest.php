<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\Document;

use KleijnWeb\SwaggerBundle\Dev\DocumentFixer\Fixers\SwaggerBundleResponseFixer;
use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use KleijnWeb\SwaggerBundle\Dev\Command\AmendSwaggerDocumentCommand;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class AmendSwaggerDocumentCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * Set up the command tester
     */
    protected function setUp()
    {
        $application = new Application();
        $application->add(new AmendSwaggerDocumentCommand(new DocumentRepository(), new SwaggerBundleResponseFixer()));

        $command = $application->find(AmendSwaggerDocumentCommand::NAME);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function willAddResponsesToDocument()
    {
        $minimalDocumentPath = __DIR__ . '/../DocumentFixer/assets/minimal.yml';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('willAddResponsesToDocument'));

        $amendedPath = vfsStream::url('willAddResponsesToDocument/modified.yml');
        $this->commandTester->execute(
            [
                'command' => AmendSwaggerDocumentCommand::NAME,
                'file'    => $minimalDocumentPath,
                '--out'   => $amendedPath
            ]
        );

        $modifiedContent = file_get_contents($amendedPath);
        $this->assertContains('responses', $modifiedContent);

        $amendedData = Yaml::parse($modifiedContent);

        $this->assertArrayHasKey('responses', $amendedData);
    }
}
