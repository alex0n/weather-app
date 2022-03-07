<?php
declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct(iterable $commands = [], string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
