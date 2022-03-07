<?php
declare(strict_types=1);

namespace App\Service\GRPC;

use Spiral\RoadRunner\GRPC;
use Paydoo\Weather\{
    EmptyRequest,
    GetWeatherByDateAndStationRequest,
    GetWeatherByDateRequest,
    LastAvailableWeatherTime,
    WeatherConditionsList
};

interface WeatherInterface extends GRPC\ServiceInterface
{
    public const NAME = 'paydoo.Weather';

    public function getWeatherByStation(
        GRPC\ContextInterface $ctx,
        GetWeatherByDateAndStationRequest $in
    ): WeatherConditionsList;

    public function getWeatherAvgStatistics(
        GRPC\ContextInterface $ctx,
        GetWeatherByDateRequest $in
    ): WeatherConditionsList;

    public function getLastAvailableWeatherDt(
        GRPC\ContextInterface $ctx,
        EmptyRequest $in
    ): LastAvailableWeatherTime;
}
