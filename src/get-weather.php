<?php declare(strict_types=1);

const ROOT_DIR = __DIR__;
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/bootstrap.php';

use App\WeatherApi;
use Symfony\Component\DependencyInjection;

/** @var DependencyInjection\ContainerBuilder $container */

///** @var \App\Service\Storage\Client $c */
//$c = $container->get(\App\Service\Storage\Client::class);
//$r = $c->getWeatherAvgStatistics(new \DateTime('2022-03-01'));
//var_dump($r);die;

/**
 * in real app GrpcClient will be resolved via WeatherApi\ClientInterface
 */
/** @var WeatherApi\GrpcClient $client */
$client = $container->get(WeatherApi\GrpcClient::class);

/**
 * Information about temperature, humidity and wind from station 1, for given date and time
 */
$weatherStationId = 'ws1';
$weatherReportDt = new \DateTime('2022-03-01');

$weatherConditionsList = $client->getWeatherByDateAndStation($weatherReportDt, $weatherStationId);

echo sprintf(
    'Weather for station "%s" and date "%s":',
    $weatherStationId,
        $weatherReportDt->format('Y-m-d')
    ) . PHP_EOL;

$recordIndex = 0;
foreach ($weatherConditionsList->all() as $weatherConditionsItem) {
    echo sprintf(
        '%d. %s: Temperature: %.2f c; Humidity: %.2f %%; Wind: %.2f km/s',
            ++$recordIndex,
            $weatherConditionsItem->getDateTime()->format('d-m-Y H:i'),
            $weatherConditionsItem->getTemperature(),
            $weatherConditionsItem->getHumidity(),
            $weatherConditionsItem->getWind()
        ) . PHP_EOL;
}

echo str_repeat('=', 20) . PHP_EOL;

/**
 * Information about temperature, humidity and wind from station 1, for given date and time
 */
//$weatherStationId = 'ws2';
//$weatherDt = new \DateTime('2022-03-01');
//$weatherConditionsList = $client->getWeatherByDateAndStation($weatherReportDt, $weatherStationId);

echo sprintf(
        'Weather for station "%s" and date "%s":',
        $weatherStationId,
        $weatherReportDt->format('Y-m-d')
    ) . PHP_EOL;

$recordIndex = 0;
foreach ($weatherConditionsList->all() as $weatherConditionsItem) {
    echo sprintf(
            '%d. %s: Temperature: %.2f c; Humidity: %.2f %%; Wind: %.2f km/s',
            ++$recordIndex,
            $weatherConditionsItem->getDateTime()->format('d-m-Y H:i'),
            $weatherConditionsItem->getTemperature(),
            $weatherConditionsItem->getHumidity(),
            $weatherConditionsItem->getWind()
        ) . PHP_EOL;
}

echo str_repeat('=', 20) . PHP_EOL;

/**
 * Getting Averaged information about temperature, humidity and wind from both stations, for given date
 */
$weatherReportDt = new \DateTime('2022-03-01');

$weatherConditionsList = $client->getWeatherAvgStatistics($weatherReportDt);

foreach ($weatherConditionsList->all() as $weatherConditionsItem) {
    echo sprintf(
            'Avg weather stats for %s station "%s":',
            $weatherReportDt->format('Y-m-d'),
            $weatherConditionsItem->getStationId()
        ) . PHP_EOL;
    echo sprintf('1. Temperature: %.2f celsius', $weatherConditionsItem->getTemperature()) . PHP_EOL;
    echo sprintf('2. Humidity: %.2f %%', (float)$weatherConditionsItem->getHumidity()) . PHP_EOL;
    echo sprintf('3. Wind: %.2f km/h', $weatherConditionsItem->getWind()) . PHP_EOL;

    echo str_repeat('=', 20) . PHP_EOL;
}

/**
 * Getting weather last available date/time
 */
$lastWeatherRecordDt = $client->getLastAvailableWeatherDt();

echo sprintf('Last available weather record date/time: %s', $lastWeatherRecordDt->format('d-m-Y H:i')) . PHP_EOL;
