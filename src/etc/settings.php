<?php

declare(strict_types=1);

use Monolog\Logger;

return [
    'displayErrorDetails' => true,
    'logger' => [
        'name' => 'slim-app',
        'path' => __DIR__ . '/../../logs/app.log',
        'level' => Logger::DEBUG,
    ],
    'session' => [],
    'translator' => [
        'defaultlanguagecode' => 'de_DE',
    ],
    'twig' => [
        'strict_variables' => false,
        'auto_reload' => true,
        'debug' => true,
        'optimizations' => -1,
        'templatesettings' => [],
        'cachemode' => 1,
    ]
];
