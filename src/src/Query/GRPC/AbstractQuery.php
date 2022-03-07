<?php
declare(strict_types=1);

namespace App\Query\GRPC;

use InfluxDB2;

abstract class AbstractQuery
{
    protected const BUCKET_WEATHER = 'weather';

    public function __construct(protected InfluxDB2\Client $client)
    {
    }
}
