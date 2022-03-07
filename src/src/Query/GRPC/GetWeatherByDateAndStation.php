<?php
declare(strict_types=1);

namespace App\Query\GRPC;

use App\Model\WeatherConditions;
use DateInterval;
use DateTime;
use DateTimeInterface;

final class GetWeatherByDateAndStation extends AbstractQuery
{
    public function __invoke(
        DateTimeInterface $dateTime,
        string $stationId
    ): WeatherConditions\ItemList {
        $dtFrom = (clone $dateTime->setTime(0, 0, 0))->format(DateTimeInterface::ATOM);
        $dtTill = (clone $dateTime->setTime(0, 0, 0))
            ->add(new DateInterval('P1D'))->format(DateTimeInterface::ATOM);

        $influxdbQuery = str_replace(
            ['{bucket}', '{dtFrom}', '{dtTill}', '{stationId}'],
            [AbstractQuery::BUCKET_WEATHER, $dtFrom, $dtTill, $stationId],
            '
        from(bucket: "{bucket}")
            |> range(start: {dtFrom}, stop: {dtTill})
            |> filter(fn: (r) => r["_measurement"] == "conditions")
            |> filter(fn: (r) => r["station"] == "{stationId}")
            |> keep(columns: ["_time", "_value", "station", "_field"])
            |> pivot(rowKey:["_time"], columnKey: ["_field"], valueColumn: "_value")
            |> sort(columns:["_time"])
            '
        );

        $queryResults = $this->client->createQueryApi()->query($influxdbQuery);

        $list = new WeatherConditions\ItemList();

        foreach ($queryResults as $table) {
            foreach ($table->records as $record) {
                [
                    '_time' => $datetimeString,
                    'station' => $stationId,
                    'humidity' => $humidity,
                    'temperature' => $temperature,
                    'wind' => $wind,
                ] = $record;

                $recordDt = DateTime::createFromFormat(DateTimeInterface::ATOM, $datetimeString);
                $item = new WeatherConditions\item();
                $item
                    ->setDateTime($recordDt)
                    ->setStationId($stationId)
                    ->setTemperature($temperature)
                    ->setHumidity($humidity)
                    ->setWind($wind);
                $list->add($item);
            }
        }

        return $list;
    }
}
