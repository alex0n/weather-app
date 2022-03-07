<?php
declare(strict_types=1);

namespace App\Service\GRPC;

use Paydoo\Weather\{
    EmptyRequest,
    GetWeatherByDateAndStationRequest,
    GetWeatherByDateRequest,
    WeatherConditionsList,
    WeatherInfo,
    LastAvailableWeatherTime
};

use Google\Protobuf\Timestamp;
use Spiral\RoadRunner\GRPC;
use App\Model\WeatherConditions;
use App\Storage;
use Psr\Log\LoggerInterface;

class Weather implements WeatherInterface
{
    protected $storageClient;

    public function __construct(
        Storage\ClientFactory $storageClientFactory,
        private LoggerInterface $logger
    ) {
        $this->storageClient = $storageClientFactory->create();
    }

    public function getWeatherByStation(
        GRPC\ContextInterface $ctx,
        GetWeatherByDateAndStationRequest $in
    ): WeatherConditionsList {
        $this->logger->info('[GRPC Server] Got request', [
            'label' => 'GRPC Server',
            'action' => __METHOD__,
            'request' => $in->serializeToJsonString(),
            'author' => __CLASS__,
        ]);

        $dateTime = $in->getTimestamp()?->toDateTime();

        $weatherConditionsList = $this->storageClient->getWeatherByDateAndStation($dateTime, $in->getStationId());
        $list = new WeatherConditionsList();

        $weatherConditions = array_map(static function (WeatherConditions\Item $weatherConditionsItem) {
            return new WeatherInfo([
                'timestamp' => new Timestamp(['seconds' => $weatherConditionsItem->getDateTime()->getTimestamp()]),
                'stationId' => $weatherConditionsItem->getStationId(),
                'temperature' => $weatherConditionsItem->getTemperature(),
                'humidity' => $weatherConditionsItem->getHumidity(),
                'wind' => $weatherConditionsItem->getWind(),
            ]);
        }, $weatherConditionsList->all());

        $list->setConditions($weatherConditions);

        $this->logger->info('[GRPC Server] Sent response', [
            'label' => 'GRPC Server',
            'action' => __METHOD__,
            'response' => $list->serializeToJsonString(),
            'author' => __CLASS__,
        ]);

        return $list;
    }

    public function getWeatherAvgStatistics(
        GRPC\ContextInterface $ctx,
        GetWeatherByDateRequest $in
    ): WeatherConditionsList {
        $this->logger->info('[GRPC Server] Got request', [
            'label' => 'GRPC Server',
            'action' => __METHOD__,
            'request' => $in->serializeToJsonString(),
            'author' => __CLASS__,
        ]);

        $dateTime = $in->getTimestamp()?->toDateTime();

        $weatherConditionsList = $this->storageClient->getWeatherAvgStatistics($dateTime);

        $list = new WeatherConditionsList();

        $weatherConditions = array_map(static function (WeatherConditions\Item $weatherConditionsItem) {
            return new WeatherInfo([
                'timestamp' => new Timestamp(['seconds' => $weatherConditionsItem->getDateTime()->getTimestamp()]),
                'stationId' => $weatherConditionsItem->getStationId(),
                'temperature' => $weatherConditionsItem->getTemperature(),
                'humidity' => $weatherConditionsItem->getHumidity(),
                'wind' => $weatherConditionsItem->getWind(),
            ]);
        }, $weatherConditionsList->all());

        $list->setConditions($weatherConditions);

        $this->logger->info('[GRPC Server] Sent response', [
            'label' => 'GRPC Server',
            'action' => __METHOD__,
            'response' => $list->serializeToJsonString(),
            'author' => __CLASS__,
        ]);

        return $list;
    }

    public function getLastAvailableWeatherDt(
        GRPC\ContextInterface $ctx,
        EmptyRequest $in
    ): LastAvailableWeatherTime {
        $this->logger->info('[GRPC Server] Got request', [
            'label' => 'GRPC Server',
            'action' => __METHOD__,
            'request' => $in->serializeToJsonString(),
            'author' => __CLASS__,
        ]);

        $lastAvailableWeatherDt = $this->storageClient->getLastAvailableWeatherDt();
        $timestamp = new Timestamp();
        $timestamp->fromDateTime($lastAvailableWeatherDt);

        $response = new LastAvailableWeatherTime(['timestamp' => $timestamp]);

        $this->logger->info('[GRPC Server] Sent response', [
            'label' => 'GRPC Server',
            'action' => __METHOD__,
            'response' => $response->serializeToJsonString(),
            'author' => __CLASS__,
        ]);

        return $response;
    }
}
