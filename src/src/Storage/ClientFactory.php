<?php
declare(strict_types=1);

namespace App\Storage;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use InvalidArgumentException;

final class ClientFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private const INFLUXDB = 'influxdb';

    public function create(?string $clientName = self::INFLUXDB): ClientInterface
    {
        /**
         * take client from configuration
         */
        return match ($clientName) {
            self::INFLUXDB => $this->container->get(InfluxDB\Client::class),
            default => static function () {
                throw new InvalidArgumentException('Unable to resolve storage client');
            },
        };
    }
}
