<?php

declare(strict_types=1);

use Slim\Middleware\Session as SessionMiddleware;
use horstoeko\slimapp\middleware\SlimAppMiddlewareLocale;

return [
    SessionMiddleware::class,
    SlimAppMiddlewareLocale::class,
];
