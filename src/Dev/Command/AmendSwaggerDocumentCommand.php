<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Dev\Command;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class AmendSwaggerDocumentCommand extends Command
{
    const NAME = 'swagger:document:amend';

    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @param DocumentRepository $documentRepository
     */
    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;

        parent::__construct(self::NAME);

        $this
            ->setDescription('Amend a Swagger definition with predefined SwaggerBundle responses')
            ->setHelp('This is a development tool and will only work with require-dev dependencies included');
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
