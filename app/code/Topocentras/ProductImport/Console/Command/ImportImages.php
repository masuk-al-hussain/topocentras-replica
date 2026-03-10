<?php

namespace Topocentras\ProductImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Topocentras\ProductImport\Service\ImageImporter;
use Magento\Framework\App\State;

class ImportImages extends Command
{
    const ARGUMENT_FILE = 'file';
    const OPTION_BATCH_SIZE = 'batch-size';
    const OPTION_FORCE = 'force';
    const OPTION_OFFSET = 'offset';
    const OPTION_LIMIT = 'limit';

    private $imageImporter;
    private $appState;

    public function __construct(
        ImageImporter $imageImporter,
        State $appState,
        ?string $name = null
    ) {
        $this->imageImporter = $imageImporter;
        $this->appState = $appState;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('topocentras:product:import-images')
            ->setDescription('Import product images from CSV feed')
            ->addArgument(
                self::ARGUMENT_FILE,
                InputArgument::REQUIRED,
                'Path to CSV file'
            )
            ->addOption(
                self::OPTION_BATCH_SIZE,
                'b',
                InputOption::VALUE_OPTIONAL,
                'Batch size for processing',
                50
            )
            ->addOption(
                self::OPTION_FORCE,
                'f',
                InputOption::VALUE_NONE,
                'Force re-download images even if product has images'
            )
            ->addOption(
                self::OPTION_OFFSET,
                'o',
                InputOption::VALUE_OPTIONAL,
                'Skip first N products (resume from specific position)',
                0
            )
            ->addOption(
                self::OPTION_LIMIT,
                'l',
                InputOption::VALUE_OPTIONAL,
                'Process only N products (0 = all)',
                0
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
        }

        $filePath = $input->getArgument(self::ARGUMENT_FILE);
        $batchSize = (int) $input->getOption(self::OPTION_BATCH_SIZE);
        $force = $input->getOption(self::OPTION_FORCE);
        $offset = (int) $input->getOption(self::OPTION_OFFSET);
        $limit = (int) $input->getOption(self::OPTION_LIMIT);

        $output->writeln('<info>Starting image import...</info>');
        $output->writeln('<info>File: ' . $filePath . '</info>');
        $output->writeln('<info>Batch size: ' . $batchSize . '</info>');
        $output->writeln('<info>Force re-download: ' . ($force ? 'Yes' : 'No') . '</info>');
        if ($offset > 0) {
            $output->writeln('<info>Offset: Skip first ' . $offset . ' products</info>');
        }
        if ($limit > 0) {
            $output->writeln('<info>Limit: Process only ' . $limit . ' products</info>');
        }
        $output->writeln('');

        try {
            $stats = $this->imageImporter->importImagesFromCsv($filePath, $batchSize, $output, $force, $offset, $limit);

            $output->writeln('');
            $output->writeln('<info>Image import completed!</info>');
            $output->writeln('<info>Statistics:</info>');
            $output->writeln('  Total products processed: ' . $stats['total']);
            $output->writeln('  Images downloaded: ' . $stats['downloaded']);
            $output->writeln('  Images skipped: ' . $stats['skipped']);
            $output->writeln('  Errors: ' . $stats['errors']);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Image import failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
