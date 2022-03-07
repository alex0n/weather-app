<?php
declare(strict_types=1);

namespace App\Service;

class MetricsConverter
{
    public function convertFarenheitsToCelsius(float $farenheits): float
    {
        return ($farenheits - 32) * 5 / 9;
    }

    public function convertInchesToMillimeters(float $inches): float
    {
        return $inches * 25.4;
    }

    public function convertMilesToKilometres(float $miles): float
    {
        return $miles * 1.609;
    }
}
