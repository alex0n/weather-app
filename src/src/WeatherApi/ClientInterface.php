<?php
declare(strict_types=1);

namespace App\WeatherApi;

use App\Model\WeatherConditions;
use DateTimeInterface;
use DateTime;

interface ClientInterface
{
    public function getWeatherByDateAndStation(
        DateTimeInterface $dateTime,
        string $weatherStationId
    ): WeatherConditions\ItemList;

    public function getWeatherAvgStatistics(DateTime $reportDateTime): WeatherConditions\ItemList;

    public function getLastAvailableWeatherDt(): DateTime;
}
