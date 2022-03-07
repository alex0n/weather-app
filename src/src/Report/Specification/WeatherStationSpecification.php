<?php
declare(strict_types=1);

namespace App\Report\Specification;

use SplFileInfo;

interface WeatherStationSpecification
{
    public function isSatisfiedBy(SplFileInfo $file): bool;
}
