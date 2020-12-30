<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

$settings = require __DIR__ . '/../etc/settings.php';
$settings($containerBuilder);

$services = require __DIR__ . '/../etc/services.php';
$services($containerBuilder);

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$middleware = require __DIR__ . '/../etc/middleware.php';
$middleware($app);

$routes = require __DIR__ . '/../etc/routes.php';
$routes($app);

/** @var bool $displayErrorDetails */
$displayErrorDetails = $container->get('settings')['displayErrorDetails'] ?? false;

$app->addRoutingMiddleware();
$app->addErrorMiddleware($displayErrorDetails, true, true, $logger);

$app->run();
