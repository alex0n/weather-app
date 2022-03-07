<?php
declare(strict_types=1);

namespace App\Query\GRPC;

use DateTime;

final class GetLastAvailableWeatherDt extends AbstractQuery
{
    public function __invoke(): DateTime
    {
        $influxdbQuery = str_replace(['{bucket}'], [AbstractQuery::BUCKET_WEATHER], '
            from(bucket: "{bucket}")
              |> range(start: 0, stop: now())
              |> filter(fn: (r) => r["_measurement"] == "conditions")
              |> keep(columns: ["_time"])
              |> sort(columns: ["_time"], desc: false)
              |> last(column: "_time")
              ');

        $queryResults = $this->client->createQueryApi()->query($influxdbQuery);

        return new DateTime($queryResults[0]->records[0]['_time']);
    }
}
