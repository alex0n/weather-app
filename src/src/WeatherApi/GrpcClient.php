<?php
declare(strict_types=1);

namespace App\WeatherApi;

use Grpc\ChannelCredentials;
use Paydoo\Weather as PaydooWeather;
use Google\Protobuf;
use App\Exception;
use App\Model\WeatherConditions;
use Psr\Log\LoggerInterface;
use const Grpc\STATUS_OK;
use DateTimeInterface;
use DateTime;
use stdClass;

class GrpcClient implements ClientInterface
{
    private $client;

    public function __construct(string $host, protected LoggerInterface $logger)
    {
        /**
         * @todo inject client instead of creating here
         */
        $this->client = new PaydooWeather\WeatherClient($host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }

    /**
     * @throws Exception\Grpc
     */
    public function getWeatherByDateAndStation(
        DateTimeInterface $dateTime,
        string $weatherStationId
    ): WeatherConditions\ItemList {
        $weatherDateTimestamp = new Protobuf\Timestamp();
        $weatherDateTimestamp->fromDateTime($dateTime);

        $weatherByAndStationRequest = new PaydooWeather\GetWeatherByDateAndStationRequest();
        $weatherByAndStationRequest
            ->setStationId($weatherStationId)
            ->setTimestamp($weatherDateTimestamp);

        $this->logger->info('[Grpc client] Retrieving weather conditions from Grpc API by date and station', [
            'label' => 'Grpc client',
            'dt' => $dateTime->format('Y-m-d'),
            'stationId' => $weatherStationId,
            'author' => __CLASS__,
        ]);

        [$response, $status] = $this->client->getWeatherByStation($weatherByAndStationRequest)->wait();
        /** @var null|array|PaydooWeather\WeatherConditionsList $response */
        $this->logger->info(
            '[Grpc client] Received response from Grpc API',
            [
                'label' => 'Grpc client',
                'response' => $response?->serializeToJsonString(),
                'status' => json_encode($status),
                'author' => __CLASS__,
            ]
        );

        $this->handleResponseStatus($status);

        $weatherConditionsItems = [];

        foreach ($response->getConditions() as $weatherInfo) {
            /** @var PaydooWeather\WeatherInfo $weatherInfo */
            $recordDt = DateTime::createFromFormat('U', (string)$weatherInfo->getTimestamp()?->getSeconds());
            $item = new WeatherConditions\Item();
            $item
                ->setDateTime($recordDt)
                ->setTemperature($weatherInfo->getTemperature())
                ->setHumidity($weatherInfo->getHumidity())
                ->setWind($weatherInfo->getWind());

            $weatherConditionsItems[] = $item;
        }

        return new WeatherConditions\ItemList($weatherConditionsItems);
    }

    /**
     * @throws Exception\Grpc
     */
    public function getWeatherAvgStatistics(DateTime $reportDateTime): WeatherConditions\ItemList
    {
        $weatherDateTimestamp = new Protobuf\Timestamp();
        $weatherDateTimestamp->fromDateTime($reportDateTime);

        $weatherByDateRequest = new PaydooWeather\GetWeatherByDateRequest();
        $weatherByDateRequest
            ->setTimestamp($weatherDateTimestamp);

        $this->logger->info(
            '[Grpc client] Retrieving weather average conditions from Grpc API by date',
            [
                'label' => 'Grpc client',
                'dt' => $reportDateTime->format('Y-m-d'),
                'author' => __CLASS__,
            ]
        );

        [$response, $status] = $this->client->getWeatherAvgStatistics($weatherByDateRequest)->wait();
        /** @var null|PaydooWeather\WeatherConditionsList $response */
        $this->logger->info(
            '[Grpc client] Received response from Grpc API',
            [
                'label' => 'Grpc client',
                'response' => $response?->serializeToJsonString(),
                'status' => json_encode($status),
                'author' => __CLASS__,
            ]
        );

        $this->handleResponseStatus($status);

        $weatherConditionsItems = [];

        foreach ($response->getConditions() as $weatherInfo) {
            /** @var PaydooWeather\WeatherInfo $weatherInfo */
            $item = new WeatherConditions\Item();
            $item
                ->setDateTime($reportDateTime) // @todo take report dt from query results
                ->setStationId($weatherInfo->getStationId())
                ->setTemperature($weatherInfo->getTemperature())
                ->setHumidity($weatherInfo->getHumidity())
                ->setWind($weatherInfo->getWind());

            $weatherConditionsItems[] = $item;
        }

        return new WeatherConditions\ItemList($weatherConditionsItems);
    }

    /**
     * @throws Exception\Grpc
     */
    public function getLastAvailableWeatherDt(): DateTime
    {
        $emptyRequest = new PaydooWeather\EmptyRequest();

        $this->logger->info(
            '[Grpc client] Retrieving weather conditions last available datetime from Grpc API',
            [
                'label' => 'Grpc client',
                'author' => __CLASS__,
            ]
        );

        [$response, $status] = $this->client->getLastAvailableWeatherDt($emptyRequest)->wait();
        /** @var null|PaydooWeather\LastAvailableWeatherTime $response */
        $this->logger->info(
            '[Grpc client] Received response from Grpc API',
            [
                'label' => 'Grpc client',
                'response' => $response?->serializeToJsonString(),
                'status' => json_encode($status),
                'author' => __CLASS__,
            ]
        );
        $this->handleResponseStatus($status);

        $lastWeatherRecordTimestamp = $response->getTimestamp()?->getSeconds();

        return DateTime::createFromFormat('U', (string)$lastWeatherRecordTimestamp);
    }

    private function handleResponseStatus(stdClass $status): void
    {
        if ($status->code !== STATUS_OK) {
            throw Exception\Grpc::requestFailed($status->code, $status->details);
        }
    }
}
