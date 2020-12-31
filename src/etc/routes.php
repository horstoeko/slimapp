<?php

declare(strict_types=1);

use horstoeko\slimapp\baseapp\action\HtmlIndexAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
        "callback" => HtmlIndexAction::class
    ],
];
