<?php declare(strict_types=1);

ini_set('display_errors', 'stderr'); // for roadrunner logs

const ROOT_DIR = __DIR__;
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/bootstrap.php';

use Symfony\Component\DependencyInjection;
use Spiral\RoadRunner;
use Spiral\Goridge;
use App\Service\GRPC\Weather as WeatherService;
use App\Service\GRPC\WeatherInterface as WeatherServiceInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

/** @var DependencyInjection\ContainerBuilder $container */

$server = new RoadRunner\GRPC\Server(null, [
    'debug' => $container->getParameter('grpc.debug'), // optional (default: false)
]);

$server->registerService(WeatherServiceInterface::class, $container->get(WeatherService::class));

/** @var Logger $logger */
$logger = $container->get(LoggerInterface::class);
$logger->info('[GRPC Server] Worker started');

$worker = new RoadRunner\Worker(new Goridge\StreamRelay(STDIN, STDOUT));
//$worker = RoadRunner\Worker::create();
$server->serve();
