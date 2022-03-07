<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;
use function sprintf;

final class Grpc extends Exception
{
    public static function requestFailed(int $code, string $details): self
    {
        return new self(sprintf(
            'gRPC request failed : error code: %d, details: %s',
            $code,
            $details
        ));
    }
}
