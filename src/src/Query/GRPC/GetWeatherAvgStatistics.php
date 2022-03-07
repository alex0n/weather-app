<?php
declare(strict_types=1);

namespace App\Query\GRPC;

use DateTimeInterface;
use DateInterval;
use App\Model\WeatherConditions;
use stdClass;

final class GetWeatherAvgStatistics extends AbstractQuery
{
    public function __invoke(DateTimeInterface $dateTime): WeatherConditions\ItemList
    {
        $dtFrom = (clone $dateTime->setTime(0, 0, 0))->format(DateTimeInterface::ATOM);
        $dtTill = (clone $dateTime->setTime(0, 0, 0))
            ->add(new DateInterval('P1D'))->format(DateTimeInterface::ATOM);

        $influxdbQuery = str_replace(
            ['{bucket}', '{dtFrom}', '{dtTill}'],
            [AbstractQuery::BUCKET_WEATHER, $dtFrom, $dtTill],
            '
        from(bucket: "{bucket}")
          |> range(start: {dtFrom}, stop: {dtTill})
          |> filter(fn: (r) => r["_measurement"] == "conditions")
          |> filter(fn: (r) => r["_field"] == "temperature" or r["_field"] == "humidity" or r["_field"] == "wind")
          |> group(columns: ["_field", "station"])
          |> aggregateWindow(every: 24h, fn: mean, createEmpty: false)
          |> keep(columns: ["_field", "_value", "_time", "station"])
          |> yield(name: "mean")'
        );

        /**
         * @todo use parametrized queries if working with influxdb cloud
         */
//        $query = new InfluxDB2\Model\Query;
//        $query
//            ->setQuery($influxdbQuery)
//            ->setParams([
//                'dtFrom' => (clone $dateTime->setTime(0, 0, 0))->getTimestamp(),
//                'dtTill' => (clone $dateTime->setTime(23, 59, 59))->getTimestamp(),
//            ]);

        $queryResults = $this->client->createQueryApi()->query($influxdbQuery);

        $statistics = [];

        foreach ($queryResults as $table) {
            foreach ($table->records as $record) {
                ['_field' => $metric, 'station' => $station, '_value' => $average] = $record;

                $statistics[$station] ??= new stdClass();
                $statistics[$station]->stationId = $station;

                switch ($metric) {
                    case 'temperature':
                        $statistics[$station]->temperature = $average;
                        break;
                    case 'humidity':
                        $statistics[$station]->humidity = $average;
                        break;
                    case 'wind':
                        $statistics[$station]->wind = $average;
                        break;
                }
            }
        }

        $list = new WeatherConditions\ItemList();

        foreach ($statistics as $stationId => $stationData) {
            $item = new WeatherConditions\item();
            $item
                ->setDateTime($dateTime)
                ->setStationId($stationId)
                ->setTemperature($stationData->temperature)
                ->setHumidity($stationData->humidity)
                ->setWind($stationData->wind);
            $list->add($item);
        }

        return $list;
    }
}
