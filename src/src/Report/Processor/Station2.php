<?php
declare(strict_types=1);

namespace App\Report\Processor;

use App\Report\ProcessorInterface;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use App\Model\WeatherConditions;
use App\Service;
use App\Storage;
use App\Enum;
use SplFileInfo;
use DateTime;
use Exception;

class Station2 implements LoggerAwareInterface, ProcessorInterface
{
    use LoggerAwareTrait;

    protected $storageClient;

    public function __construct(
        Storage\ClientFactory $storageClientFactory,
        private Service\MetricsConverter $metricsConverter,
        private Service\CsvFileReader $csvFileReader
    ) {
        $this->storageClient = $storageClientFactory->create();
    }

    public function process(SplFileInfo $file): void
    {
        if ($file->getExtension() !== 'csv') {
            /**
             * @todo create/trow dedicated exception
             */
            throw new InvalidArgumentException(sprintf(
                'Cannot process report. Wrong file extension. Got %s, expected json',
                $file->getExtension()
            ));
        }

        $csv = $this->csvFileReader->open($file->getPathname());

        $processedRowCount = 0;

        foreach ($csv->rows() as $rowIndex => $csvRow) {
            if (!$rowIndex || !$csvRow) {
                continue;
            }
            $this->processCsvFileRecord($csvRow);
            ++$processedRowCount;
        }
        $csv->close();

        $this->logger->info(
            sprintf('Successfully processed %d records', $processedRowCount),
            [
                'label' => 'processor',
                'author' => static::class,
            ]
        );
    }

    private function processCsvFileRecord(array $record): void
    {
        [$dateTimeString, $temperature, $humidity, $wind, $rain, $light, $battery] = $record;

        $recordDt = DateTime::createFromFormat('d:m:Y H:i:s', $dateTimeString);

        $weatherConditionsItem = new WeatherConditions\Item();
        $weatherConditionsItem
            ->setDateTime($recordDt)
            ->setStationId(Enum\WeatherStation::STATION2)
            ->setTemperature((float)$temperature)
            ->setHumidity((float)$humidity)
            ->setWind((float)$wind)
            ->setRain((float)$rain)
            ->setLight((int)$light)
            ->setBattery($battery)
        ;

        try {
            $this->storageClient->saveWeatherConditionsRecord($weatherConditionsItem);
        } catch (Exception $exception) {
            $this->logger->error(
                $exception->getMessage(),
                [
                    'label' => 'processor',
                    'author' => static::class,
                    'trace' => $exception->getTraceAsString(),
                ]
            );
        }
    }
}
