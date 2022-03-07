<?php
declare(strict_types=1);

namespace App\Report;

use SplFileInfo;

interface ProcessorInterface
{
    public function process(SplFileInfo $file): void;
}
