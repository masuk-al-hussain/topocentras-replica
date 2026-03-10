<?php

namespace Topocentras\ProductImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Topocentras\ProductImport\Service\ProductImporter;
use Magento\Framework\App\State;

class ImportProducts extends Command
{
    const ARGUMENT_FILE = 'file';
    const OPTION_BATCH_SIZE = 'batch-size';
    const OPTION_SKIP_IMAGES = 'skip-images';

    private $productImporter;
    private $appState;

    public function __construct(
        ProductImporter $productImporter,
        State $appState,
        ?string $name = null
    ) {
        $this->productImporter = $productImporter;
        $this->appState = $appState;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('topocentras:product:import')
            ->setDescription('Import products from CSV feed')
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
                100
            )
            ->addOption(
                self::OPTION_SKIP_IMAGES,
                's',
                InputOption::VALUE_NONE,
                'Skip image downloads (products only)'
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
        $skipImages = $input->getOption(self::OPTION_SKIP_IMAGES);

        $output->writeln('<info>Starting product import...</info>');
        $output->writeln('<info>File: ' . $filePath . '</info>');
        $output->writeln('<info>Batch size: ' . $batchSize . '</info>');
        $output->writeln('<info>Skip images: ' . ($skipImages ? 'Yes' : 'No') . '</info>');
        $output->writeln('');

        try {
            $stats = $this->productImporter->importFromCsv($filePath, $batchSize, $output, $skipImages);

            $output->writeln('');
            $output->writeln('<info>Import completed!</info>');
            $output->writeln('<info>Statistics:</info>');
            $output->writeln('  Total rows processed: ' . $stats['total']);
            $output->writeln('  Products created: ' . $stats['created']);
            $output->writeln('  Products updated: ' . $stats['updated']);
            $output->writeln('  Products skipped: ' . $stats['skipped']);
            $output->writeln('  Errors: ' . $stats['errors']);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Import failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
