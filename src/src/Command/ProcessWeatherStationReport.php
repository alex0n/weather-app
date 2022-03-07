<?php
declare(strict_types=1);

namespace App\Command;

use App\Exception;
use InfluxDB2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function in_array;
use DateTime;
use DirectoryIterator;

final class ProcessWeatherStationReport extends AbstractCommand
{
    private const ALLOWED_REPORT_EXTENSIONS = ['json', 'csv'];

    protected static $defaultName = 'app:process-ws-report';

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Process weather station reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * cant use glob() because GLOB_BRACE is unavailable under Alpine linux
         *
         * @todo consider using symfony/finder package here
         */
        $fileIndex = 0;

        foreach (new DirectoryIterator($this->reportsPath) as $item) {
            /** @var DirectoryIterator $item */
            if (
                !$item->isDot()
                && $item->isFile()
                && in_array($item->getExtension(), self::ALLOWED_REPORT_EXTENSIONS, true)
            ) {
                ++$fileIndex;
                $fileIndexLabel = sprintf('[File #%d]', $fileIndex);
                $this->logInfo(sprintf('%s Processing file: %s', $fileIndexLabel, $item->getRealPath()));

                try {
                    $processor = $this->processorResolver->resolve($item->getFileInfo());
                } catch (Exception\Processor $exception) {
                    $this->logInfo(sprintf(
                        '%s Report processing error: %s. Skipping report',
                        $fileIndexLabel,
                        $exception->getMessage()
                    ));
                    $failedFilePath = sprintf('%s/failed/%s', $this->reportsPath, $item->getFilename());
                    rename($item->getRealPath(), $failedFilePath);
                    $this->logInfo(sprintf('%s Moving failed file to: %s', $fileIndexLabel, $failedFilePath));
                    continue;
                }
                $this->logInfo(sprintf('%s Processor resolved: %s', $fileIndexLabel, $processor::class));
                $processor->process($item->getFileInfo());

                $processedFilePath = sprintf('%s/processed/%s', $this->reportsPath, $item->getFilename());
                $this->logInfo(sprintf('%s Moving processed file to: %s', $fileIndexLabel, $processedFilePath));
                rename($item->getRealPath(), $processedFilePath);
            }
        }

        $this->logInfo('Reports processing completed');

        return Command::SUCCESS;
    }
}
