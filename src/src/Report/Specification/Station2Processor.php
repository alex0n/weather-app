<?php
declare(strict_types=1);

namespace App\Report\Specification;

use App\Service\CsvFileReader;
use function preg_match;
use SplFileInfo;

class Station2Processor implements WeatherStationSpecification
{
    public function isSatisfiedBy(SplFileInfo $file): bool
    {
        // 1. check by filename
        if (!preg_match('/^[\d+]{2}-[\d+]{2}-[\d+]{4}.csv$/', $file->getFilename())) {
            return false;
        }

        // 2. check by content type
        if (mime_content_type($file->getPathname()) !== 'text/csv') {
            return false;
        }

        // 3. check by data structure (is overkill probably)

        $csvReader = new CsvFileReader();
        $csv = $csvReader->open($file->getPathname());

        $firstReportRow = $csv->rows();
        // skip csv header if exists
        if (!preg_match(
            '/^[\d+]{2}:[\d+]{2}:[\d+]{4}\s[\d+]{2}:[\d+]{2}:[\d+]{2}$/',
            $firstReportRow?->current()[0]
        )
        ) {
            $firstReportRow = $csv->rows();
        }
        $csv->close();

        [$time,$temperature,$humidity,$wind,$rain,$light,$battery] = $firstReportRow?->current();

        if (!$firstReportRow) {
            return false;
        }

        if (
            !is_numeric($temperature)
            || !is_numeric($humidity)
            || !is_numeric($wind)
            || !is_numeric($rain)
            || !is_numeric($light)
            || !is_string($battery)
            || !preg_match('/^[\d+]{2}:[\d+]{2}:[\d+]{4}\s[\d+]{2}:[\d+]{2}:[\d+]{2}$/', $time)
        ) {
            return false;
        }

        return true;
    }
}
