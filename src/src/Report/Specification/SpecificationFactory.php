<?php
declare(strict_types=1);

namespace App\Report\Specification;

use App\Exception;
use App\Report\Processor;
use function array_key_exists;

class SpecificationFactory
{
    private array $factoryMethods;

    public function __construct()
    {
        $this->factoryMethods = [
            Processor\Station1::class => [$this, 'createStation1Specification'],
            Processor\Station2::class => [$this, 'createStation2Specification'],
        ];
    }

    public function createSpecification(string $processor): WeatherStationSpecification
    {
        if (!array_key_exists($processor, $this->factoryMethods)) {
            throw Exception\Specification::missingStrategy($processor);
        }

        $factoryMethod = $this->factoryMethods[$processor];

        return $factoryMethod();
    }

    public function createStation1Specification(): WeatherStationSpecification
    {
        return new Station1Processor();
    }

    public function createStation2Specification(): WeatherStationSpecification
    {
        return new Station2Processor();
    }
}
