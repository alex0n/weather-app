<?php
declare(strict_types=1);

namespace App\Report;

use App\Exception;
use App\Report\Specification\SpecificationFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use SplFileInfo;

final class ProcessorResolver implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private const PROCESSORS = [
        Processor\Station1::class,
        Processor\Station2::class,
    ];

    public function __construct(private SpecificationFactory $specificationFactory)
    {
    }

    public function resolve(SplFileInfo $file): ProcessorInterface
    {
        foreach (self::PROCESSORS as $stationProcessor) {
            $procesorSpecification = $this->specificationFactory->createSpecification($stationProcessor);

            if ($procesorSpecification->isSatisfiedBy($file)) {
                return $this->container->get($stationProcessor);
            }
        }

        throw Exception\Processor::cannotResolveProcessor();
    }
}
