<?php
declare(strict_types=1);

namespace App\Exception;

use App\Report\Specification\SpecificationFactory;
use function sprintf;
use Exception;

final class Specification extends Exception
{
    public static function missingStrategy(string $processor): self
    {
        return new self(sprintf(
            'Missing specification strategy for processor "%s". Factory method has to be added in %s.',
            $processor,
            SpecificationFactory::class
        ));
    }
}
