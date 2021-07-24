<?php

declare(strict_types=1);

use horstoeko\slimapp\middleware\SlimAppMiddlewareIpAddress;
use Slim\Middleware\Session as SessionMiddleware;
use horstoeko\slimapp\middleware\SlimAppMiddlewareLocale;

return [
    SessionMiddleware::class => SessionMiddleware::class,
    SlimAppMiddlewareIpAddress::class => SlimAppMiddlewareIpAddress::class,
    SlimAppMiddlewareLocale::class => SlimAppMiddlewareLocale::class,
];
