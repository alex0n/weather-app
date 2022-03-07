<?php
declare(strict_types=1);

namespace App\Report\Processor;

use App\Report\ProcessorInterface;
use InvalidArgumentException;
use App\Storage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use App\Model\WeatherConditions;
use App\Service;
use App\Enum;
use SplFileInfo;
use JsonException;
use DateTime;
use Exception;
use const JSON_THROW_ON_ERROR;

class Station1 implements LoggerAwareInterface, ProcessorInterface
{
    use LoggerAwareTrait;

    /**
     * @var Storage\InfluxDB\Client|Storage\ClientInterface
     */
    protected $storageClient;

    public function __construct(
        Storage\ClientFactory $storageClientFactory,
        private Service\MetricsConverter $metricsConverter
    ) {
        $this->storageClient = $storageClientFactory->create();
    }

    public function process(SplFileInfo $file): void
    {
        if ($file->getExtension() !== 'json') {
            /**
             * @todo create/trow dedicated exception
             */
            throw new InvalidArgumentException(sprintf(
                'Cannot process report. Wrong file extension. Got %s, expected json',
                $file->getExtension()
            ));
        }
        $json = file_get_contents($file->getPathname());

        try {
            $reportRows = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            $this->logger->error(
                sprintf('Error while processing station 1 report: %s', $exception->getMessage()),
                [
                    'label' => 'processor',
                    'author' => static::class,
                    'trace' => $exception->getTraceAsString(),
                ]
            );
            return;
        }

        if ($reportRows) {
            foreach ($reportRows as $conditionsRow) {
                $this->processJsonFileRecord($conditionsRow);
            }
        }
        $this->logger->info(
            sprintf('Successfully processed %d records', count($reportRows)),
            [
                'label' => 'processor',
                'author' => static::class,
            ]
        );
    }

    private function processJsonFileRecord(array $record): void
    {
        [
            'time' => $timestamp,
            'temperature' => $temperature,
            'humidity' => $humidity,
            'rain' => $rain,
            'wind' => $wind,
            'battery' => $battery,
        ] = $record;

        $recordDt = DateTime::createFromFormat('U', (string)$timestamp);

        $weatherConditionsItem = new WeatherConditions\Item();
        $weatherConditionsItem
            ->setDateTime($recordDt)
            ->setStationId(Enum\WeatherStation::STATION1)
            ->setTemperature($this->metricsConverter->convertFarenheitsToCelsius($temperature))
            ->setHumidity((float)$humidity)
            ->setWind($this->metricsConverter->convertMilesToKilometres($wind))
            ->setRain($this->metricsConverter->convertInchesToMillimeters($rain))
            ->setBattery($battery)
            ->setLight(null)
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
