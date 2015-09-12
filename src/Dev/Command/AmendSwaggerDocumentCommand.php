<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Dev\Command;

use KleijnWeb\SwaggerBundle\Dev\DocumentFixer\Fixer;
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
     * @var Fixer
     */
    private $fixer;

    /**
     * @param DocumentRepository $documentRepository
     * @param Fixer              $fixer
     */
    public function __construct(DocumentRepository $documentRepository, Fixer $fixer)
    {
        parent::__construct(self::NAME);

        $this
            ->setDescription('Make your Swagger definition reflect your apps in- and output')
            ->setHelp(
                "Will update your definition with predefined SwaggerBundle responses,"
                . " as well as update it to reflect any changes in your DTOs, should they exist.\n\n"
                . "This is a development tool and will only work with require-dev dependencies included"
            )
            ->addArgument('file', InputArgument::REQUIRED, 'File path to the Swagger document')
            ->addOption(
                'out',
                'o',
                InputOption::VALUE_REQUIRED,
                'Write the resulting document to this location (will overwrite existing by default'
            );

        $this->documentRepository = $documentRepository;
        $this->fixer = $fixer;
    }


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $document = $this->documentRepository->get($input->getArgument('file'));
        $this->fixer->fix($document);
        $document->write($input->getOption('out'));
    }
}
