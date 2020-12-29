<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader as SymfonyTranslatorArrayLoader;
use \Symfony\Component\Translation\Loader\PhpFileLoader as SymfonyTranslatorPhpFileLoader;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        SymfonyTranslator::class => function(ContainerInterface $c) {
            $settings = $c->get('settings');
            $translatorsettings = $settings['translator'];

            $translator = new SymfonyTranslator($translatorsettings["defaultlanguagecode"]);
            $translator->setFallbackLocales([$translatorsettings["defaultlanguagecode"]]);
            $translator->addLoader('php', new SymfonyTranslatorPhpFileLoader());
            $translator->addLoader('array', new SymfonyTranslatorArrayLoader());

            return $translator;
        },
    ]);
};
