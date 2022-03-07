<?php
declare(strict_types=1);

namespace App\Report\Specification;

use function preg_match;
use SplFileInfo;
use JsonException;
use const JSON_THROW_ON_ERROR;

class Station1Processor implements WeatherStationSpecification
{
    public function isSatisfiedBy(SplFileInfo $file): bool
    {
        // 1. check by filename
        if (!preg_match('/^[\d+]{4}-[\d+]{2}-[\d+]{2}.json$/', $file->getFilename())) {
            return false;
        }

        // 2. check by content type
        if (mime_content_type($file->getPathname()) !== 'application/json') {
            return false;
        }

        // 3. check by data structure (is overkill probably)
        $json = file_get_contents($file->getPathname());

        try {
            $reportRows = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            return false;
        }

        $firstReportRow = $reportRows[0];

        $requiredFields = ['time', 'temperature', 'humidity', 'rain', 'wind', 'battery'];

        if (
            !empty(array_diff($requiredFields, array_keys($firstReportRow)))
            || count($firstReportRow) !== count($requiredFields)
        ) {
            return false;
        }


        return true;
    }
}
