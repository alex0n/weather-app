<?php

use Symfony\Component\Config;
use Symfony\Component\DependencyInjection;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$container = new DependencyInjection\ContainerBuilder();
$container->setParameter('root_dir', ROOT_DIR);

$locator = new Config\FileLocator(ROOT_DIR . '/config');
$loader = new DependencyInjection\Loader\YamlFileLoader($container, $locator);
$loader->load('dependencies-production.yaml');

$loggerParam = $container->getParameter('logger');
$container->register(RotatingFileHandler::class, RotatingFileHandler::class)
    ->addArgument($loggerParam['file_path'])
    ->addArgument($loggerParam['max_files'])
    ->addArgument($loggerParam['level']);
$container->register(LoggerInterface::class, Logger::class)
    ->addArgument($loggerParam['channel'])
    ->addMethodCall('pushHandler', [new DependencyInjection\Reference(RotatingFileHandler::class)]);
$container->registerForAutoconfiguration(LoggerAwareInterface::class)
    ->addMethodCall('setLogger', [new DependencyInjection\Reference(LoggerInterface::class)]);

$container->compile();
