<?php
declare(strict_types=1);

namespace App\Storage\InfluxDB;

use App\Model\WeatherConditions;
use InfluxDB2;
use DateTimeInterface;
use DateTime;
use App\Query;
use App\Storage;
use Psr\Log\LoggerInterface;
use Throwable;
use Exception;

final class Client implements Storage\ClientInterface
{
    private $writeApi;

    public function __construct(
        private InfluxDB2\Client $influxDbClient,
        private LoggerInterface $logger,
    ) {
        $this->writeApi = $this->influxDbClient->createWriteApi(
        /*
         * @todo check if we need batch processing
         * https://github.com/influxdata/influxdb-client-php#batching
         */
//            ['writeType' => InfluxDB2\WriteType::BATCHING, 'batchSize' => 24]
        );
    }

    /**
     * @todo think if we need such dynamics
     */
//    public function query(RequestInterface $request): ResponseInterface
//    {
//        $query = $this->queryResolver->resolveQuery($request);
//        $result = $query->execute($request);
//        return $this->responseBulder->build($result);
//    }

    public function getWeatherByDateAndStation(
        DateTimeInterface $dateTime,
        string $stationId
    ): WeatherConditions\ItemList {
        $this->logger->info('[InfluxDB client] Retrieving weather conditions by date and station', [
            'label' => 'InfluxDB client',
            'dt' => $dateTime->format('Y-m-d'),
            'stationId' => $stationId,
            'author' => __CLASS__,
        ]);

        $query = new Query\GRPC\GetWeatherByDateAndStation($this->influxDbClient);
        return $query($dateTime, $stationId);
    }

    public function getWeatherAvgStatistics(DateTime $dateTime): WeatherConditions\ItemList
    {
        $this->logger->info('[InfluxDB client] Retrieving weather average data by date', [
            'label' => 'InfluxDB client',
            'dt' => $dateTime->format('Y-m-d'),
            'author' => __CLASS__,
        ]);

        $query = new Query\GRPC\GetWeatherAvgStatistics($this->influxDbClient);
        return $query($dateTime);
    }

    public function getLastAvailableWeatherDt(): DateTime
    {
        $query = new Query\GRPC\GetLastAvailableWeatherDt($this->influxDbClient);
        return $query();
    }

    public function saveWeatherConditionsRecord(WeatherConditions\Item $weatherConditions): void
    {
        $this->logger->info('[InfluxDB client] Saving new record', [
            'label' => 'InfluxDB client',
            'data' => $weatherConditions,
            'author' => __CLASS__,
        ]);

        $point = InfluxDB2\Point::measurement('conditions');
        $point
            ->addTag('station', $weatherConditions->getStationId())
            ->addField('temperature', $weatherConditions->getTemperature())
            ->addField('humidity', $weatherConditions->getHumidity())
            ->addField('wind', $weatherConditions->getWind())
            ->addField('rain', $weatherConditions->getRain())
            ->time((string)$weatherConditions->getDateTime()->getTimestamp());

        /**
         * @todo we can probably resolve numeric battery value to string label and set battery tag for such case
         * $batteryTag = $this->batteryTagResolver->resolve($weatherConditions->getBattery());
         */
        if (is_int($weatherConditions->getBattery())) {
            $point->addField('battery', $weatherConditions->getBattery());
        } elseif ($weatherConditions->getBattery() !== null) {
            $point->addTag('battery', $weatherConditions->getBattery());
        }

        if ($weatherConditions->getLight() !== null) {
            $point->addField('light', $weatherConditions->getLight());
        }

        try {
            $this->writeApi->write($point);
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('[InfluxDB client] Error: %s', $exception->getMessage()), [
                'label' => 'InfluxDB client',
                'trace' => $exception->getTraceAsString(),
                'author' => __CLASS__,
            ]);
            /**
             * @todo throw dedicated exception
             */
            throw new Exception('Error saving weather data into storage');
        }
    }
}
