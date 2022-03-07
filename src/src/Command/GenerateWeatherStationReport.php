<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function random_int;
use DateTimeImmutable;
use Throwable;
use DateTimeInterface;
use Exception;
use function file_exists;
use function range;
use function file_put_contents;
use const JSON_THROW_ON_ERROR;

/**
 * php bin/console app:generate-ws-report --dt=2022-03-01
 */
final class GenerateWeatherStationReport extends AbstractCommand
{
    protected static $defaultName = 'app:generate-ws-report';

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Generate fake weather station reports')
            ->setDefinition([
                new InputOption('dt', null, InputOption::VALUE_REQUIRED, 'Report date'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reportDt = new DateTimeImmutable($input->getOption('dt'));

        $this->logInfo(sprintf(
            'Generating fake weather station reports for date: %s',
            $reportDt->format('Y-m-d')
        ));

        try {
            $this->generateJsonReport($reportDt, $output);
            $this->generateCsvReport($reportDt, $output);
            $this->generateInvalidReport($reportDt, $output);
        } catch (Throwable $exception) {
            $this->logInfo(sprintf(
                'Error occured while generating weather station reports: %s',
                $exception->getMessage()
            ));
            return Command::INVALID;
        }

        $this->logInfo('Reports generation completed');

        return Command::SUCCESS;
    }

    private function generateInvalidReport(DateTimeInterface $reportDt, OutputInterface $output): void
    {
        $reportFile = \sprintf('%s/i%s.json', $this->reportsPath, $reportDt->format('Y-d-m'));
        file_put_contents(
            $reportFile,
            json_encode(['test' => 123], JSON_THROW_ON_ERROR)
        );
        $this->logInfo(sprintf('Report "%s" generated', $reportFile));
    }

    private function generateCsvReport(DateTimeInterface $reportDt, OutputInterface $output): void
    {
        $reportFile = \sprintf('%s/%s.csv', $this->reportsPath, $reportDt->format('d-m-Y'));

        if (file_exists($reportFile)) {
            $this->logError(sprintf('Report already exists at %s. Skipping.', $reportFile));
            throw new Exception('Report already exists');
        }

        $weatherData = [];

        foreach (range(0, 23) as $hour) {
            $measureDt = clone $reportDt->setTime($hour, 59, 59);
            $newRecord = [
                'time' => $measureDt->format('d:m:Y H:i:s'), // Time: dd:mm:yyyy, hh:mm:ss
                'temp' => random_int(-50, 56) + random_int(0, 90) / 100, // Temp, Celsius
                'humidity' => random_int(5, 100) + random_int(0, 90) / 100, // Humidity: percent
                'wind' => random_int(0, 100), // Wind, km per hour
                'rain' => random_int(0, 10), // Rain, mm per hour
                'light' => random_int(0, 100000), // Light, lux
                'battery' => array_rand(array_flip(['low', 'medium', 'high', 'full'])), // Battery level, enum (low, medium, high, full)
            ];
            $weatherData = [...$weatherData, $newRecord];
        }

        $memoryDataHandle = fopen('php://memory', 'r+b');
        # write out the headers
        fputcsv($memoryDataHandle, array_keys(current($weatherData)));

        foreach ($weatherData as $weatherDataItem) {
            fputcsv($memoryDataHandle, $weatherDataItem, ',', '"', '\\');
        }
        rewind($memoryDataHandle);

        $reportDataCsv = stream_get_contents($memoryDataHandle);
        fclose($memoryDataHandle);

        file_put_contents(
            $reportFile,
            $reportDataCsv
        );
        $this->logInfo(sprintf('Report "%s" generated', $reportFile));
    }

    private function generateJsonReport(DateTimeInterface $reportDt, OutputInterface $output): void
    {
        $reportFile = \sprintf('%s/%s.json', $this->reportsPath, $reportDt->format('Y-d-m'));

        if (file_exists($reportFile)) {
            $this->logError(sprintf('Report already exists at %s. Skipping.', $reportFile));
            throw new Exception('Report already exists');
        }

        $weatherData = [];

        foreach (range(0, 23) as $hour) {
            $measureDt = clone $reportDt->setTime($hour, 59, 59);
            $newRecord = [
                'time' => $measureDt->getTimestamp(),
                'temperature' => round((random_int(-50, 56) * 9 / 5) + 32 + random_int(0, 90) / 100, 2), // Fahrenheits
                'humidity' => random_int(5, 100) + random_int(0, 90) / 100, // percent
                'rain' => random_int(0, 2) / 100, // inches per hour
                'wind' => random_int(0, 231), // miles per hour
                'battery' => random_int(1, 100), // Battery percent
            ];
            $weatherData = [...$weatherData, $newRecord];
        }

        $weatherDataJson = \json_encode($weatherData);
        file_put_contents(
            $reportFile,
            $weatherDataJson
        );
        $this->logInfo(sprintf('Report "%s" generated', $reportFile));
    }
}
