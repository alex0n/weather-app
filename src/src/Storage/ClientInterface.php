<?php
declare(strict_types=1);

namespace App\Storage;

use App\Model\WeatherConditions;
use DateTimeInterface;
use DateTime;

interface ClientInterface
{
    public function getWeatherByDateAndStation(
        DateTimeInterface $dateTime,
        string $stationId
    ): WeatherConditions\ItemList;

    public function getWeatherAvgStatistics(DateTime $dateTime): WeatherConditions\ItemList;

    public function getLastAvailableWeatherDt(): DateTime;

    public function saveWeatherConditionsRecord(WeatherConditions\Item $weatherConditions): void;
}
