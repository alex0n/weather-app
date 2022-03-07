<?php
declare(strict_types=1);

namespace App\Enum;

use ReflectionException;

abstract class Enum
{
    /**
     * @throws ReflectionException
     */
    final public static function all(): array
    {
        return (new ValueLocator())->locate(static::class);
    }
}
