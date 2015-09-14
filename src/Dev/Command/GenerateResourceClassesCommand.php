<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Dev\Command;

use KleijnWeb\SwaggerBundle\Dev\Generator\ResourceGenerator;
use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class GenerateResourceClassesCommand extends ContainerAwareCommand
{
    const NAME = 'swagger:generate:resources';

    /**
     * @var ResourceGenerator
     */
    private $generator;

    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @param DocumentRepository $documentRepository
     * @param ResourceGenerator  $generator
     */
    public function __construct(DocumentRepository $documentRepository, ResourceGenerator $generator)
    {
        parent::__construct(self::NAME);

        $this
            ->setDescription('Generate DTO-like classes using the resource schema definitions in a swagger document')
            ->setHelp('This is a development tool and will only work with require-dev dependencies included')
            ->addArgument('file', InputArgument::REQUIRED, 'File path to the Swagger document')
            ->addArgument('bundle', InputArgument::REQUIRED, 'Name of the bundle you want the classes in')
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Namespace of the classes to generate (relative to the bundle namespace)',
                'Model\Resources'
            );

        $this->documentRepository = $documentRepository;
        $this->generator = $generator;
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
        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $bundle = $kernel->getBundle($input->getArgument('bundle'));
        $document = $this->documentRepository->get($input->getArgument('file'));
        $this->generator->setSkeletonDirs(__DIR__ . '/../Resources/skeleton');
        $this->generator->generate($bundle, $document, $input->getOption('namespace'));
    }
}
