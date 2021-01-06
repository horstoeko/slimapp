<?php

declare(strict_types=1);

use Monolog\Logger;

return [
    'displayErrorDetails' => true,
    'logger' => [
        'name' => 'slim-app',
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
    ],
    'locale' => [
        'availablelanguagecodes' => ['de_DE', 'en_GB'],
        'defaultlanguagecode' => 'de_DE',
        'dosetlocale' => true,
        'overridelanguagecode' => null,
        'strictmatchlanguagecode' => false,
        'languagelocalemaps' => ['de_DE' => 'de_DE.utf8'],
        'unknownlanguagecodemaps' => ['en_EN' => 'en_GB'],
    ],
];
