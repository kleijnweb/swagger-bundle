<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use KleijnWeb\SwaggerBundle\Dev\Command\AmendSwaggerDocumentCommand;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class AmendSwaggerDocumentCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canExecute()
    {
        $application = new Application();
        $application->add(new AmendSwaggerDocumentCommand(new DocumentRepository()));

        $command = $application->find(AmendSwaggerDocumentCommand::NAME);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }
}
