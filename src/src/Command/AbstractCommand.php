<?php
declare(strict_types=1);

namespace App\Command;

use App\Report\ProcessorResolver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use InfluxDB2;

abstract class AbstractCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected string $reportsPath,
        protected ProcessorResolver $processorResolver,
        protected OutputInterface $output
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function logInfo(string $message): void
    {
        /**
         * @todo solve writing to both channels by monolog config
         */
        $this->output->writeln($message);
        $this->logger->info($message, ['label' => 'Console', 'author' => static::class]);
    }

    protected function logError(string $message): void
    {
        /**
         * @todo solve writing to both channels by monolog config
         */
        $this->output->writeln($message);
        $this->logger->error($message, ['label' => 'Console', 'author' => static::class]);
    }
}
