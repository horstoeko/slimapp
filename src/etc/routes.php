<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use horstoeko\slimapp\baseapp\action\HtmlIndexAction;
use Psr\Http\Message\ServerRequestInterface as Request;
use horstoeko\slimapp\middleware\SlimAppMiddlewareBasicAuth;

return [
    "cors_preflight" => [
        "method" => 'OPTIOMS',
        "pattern" => '/{routes:.*}',
        "callback" => function (Request $request, Response $response) {
            return $response;
        }
    ],
    "root" => [
        "method" => "GET",
        "pattern" => '/',
        "callback" => HtmlIndexAction::class,
        "middlewares" => [
        ]
    ],
];
