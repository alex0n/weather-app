<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

final class Processor extends Exception
{
    public static function cannotResolveProcessor(): self
    {
        return new self('Can not resolve processor');
    }
}
