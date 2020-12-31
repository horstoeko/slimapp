<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader as SymfonyTranslatorArrayLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader as SymfonyTranslatorPhpFileLoader;
use horstoeko\slimapp\twig\SlimAppTwig;
use horstoeko\slimapp\twig\SlimAppTwigApcCache;
use horstoeko\slimapp\twig\SlimAppTwigApcuCache;
use Twig\Extra\Intl\IntlExtension as TwigIntlExtension;
use Twig\Extra\Markdown\MarkdownExtension as TwigMarkdownExtension;
use Twig\Extra\Html\HtmlExtension as TwigHtmlExtension;
use Twig\Extension\DebugExtension as TwigDebugExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension as SymfonyTwigBridgeTranslationExtension;
use Twig\RuntimeLoader\RuntimeLoaderInterface as TwigRuntimeLoaderInterface;
use Twig\Extra\Markdown\DefaultMarkdown as TwigDefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime as TwigMarkdownRuntime;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\Middleware\Session as SessionMiddleware;
use SlimSession\Helper as SessionHelper;
use horstoeko\slimapp\middleware\SlimAppMiddlewareLocale;
use horstoeko\slimapp\security\SlimAppLoginManager;

return [
    LoggerInterface::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $loggerSettings = $settings['logger'];

        $logger = new Logger($loggerSettings['name']);
        $processor = new UidProcessor();
        $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);

        $logger->pushProcessor($processor);
        $logger->pushHandler($handler);

        return $logger;
    },

    SessionMiddleware::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $sessionSettings = $settings['session'];

        return new SessionMiddleware($sessionSettings);
    },

    SessionHelper::class => function () {
        return new SessionHelper();
    },

    SymfonyTranslator::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $translatorSettings = $settings['translator'];

        $translator = new SymfonyTranslator($translatorSettings["defaultlanguagecode"]);
        $translator->setFallbackLocales([$translatorSettings["defaultlanguagecode"]]);
        $translator->addLoader('php', new SymfonyTranslatorPhpFileLoader());
        $translator->addLoader('array', new SymfonyTranslatorArrayLoader());

        return $translator;
    },

    SlimAppTwig::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $twigSettings = $settings['twig'];

        switch ($twigSettings["cachemode"] ?? 0) {
            case 1:
                $cache = __DIR__ . "/../../var/twig";
                if (!is_dir($cache) || !is_writeable($cache)) {
                    $cache = false;
                }
                break;
            case 2:
                if (!extension_loaded('apc') || !ini_get('apc.enabled') || !ini_get('allow_url_include')) {
                    $cache = false;
                } else {
                    $cache = new SlimAppTwigApcCache($twigSettings["cachenamespace"] ?? "slimapp");
                }
                break;
            case 3:
                if (!extension_loaded('apcu') || !ini_get('apc.enabled') || !ini_get('allow_url_include')) {
                    $cache = false;
                } else {
                    $cache = new SlimAppTwigApcuCache($twigSettings["cachenamespace"] ?? "slimapp");
                }
                break;
            default:
                $cache = false;
        }

        $directories = [];

        if (is_dir(__DIR__ . "/../baseapp/html")) {
            $directories["slimbaseapp"] = __DIR__ . "/../baseapp/html";
        }

        if (is_dir(__DIR__ . "/../../app/html")) {
            $directories["slimapp"] = __DIR__ . "/../../app/html";
        }

        $view = new SlimAppTwig(
            $directories,
            [
                'cache' => $cache,
                'strict_variables' => $twigSettings["strict_variables"] ?? false,
                'auto_reload' => $twigSettings["auto_reload"] ?? true,
                'debug' => $twigSettings["debug"] ?? false,
                'optimizations' => $twigSettings["optimizations"] ?? -1,
            ]
        );

        $view->addExtension(new TwigIntlExtension());
        $view->addExtension(new TwigMarkdownExtension());
        $view->addExtension(new TwigHtmlExtension());
        $view->addExtension(new TwigDebugExtension());
        $view->addExtension(new SymfonyTwigBridgeTranslationExtension($c->get(SymfonyTranslator::class)));

        $view->addRuntimeLoader(
            new class implements TwigRuntimeLoaderInterface
            {
                public function load($class)
                {
                    if (TwigMarkdownRuntime::class === $class) {
                        return new TwigMarkdownRuntime(new TwigDefaultMarkdown());
                    }
                }
            }
        );

        return $view;
    },

    Capsule::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $dbSettings = $settings['db'];

        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'    => $dbSettings['driver'],
            'host'      => $dbSettings['host'],
            'database'  => $dbSettings['database'],
            'username'  => $dbSettings['username'],
            'password'  => $dbSettings['password'],
            'charset'   => $dbSettings['charset'],
            'collation' => $dbSettings['collation'],
            'prefix'    => $dbSettings['prefix'] != '' ? $dbSettings['prefix'] : '',
            'port'      => $dbSettings['port']
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    },

    SlimAppLoginManager::class => function (Capsule $capsule, SessionHelper $sessionHelper) {
        return new SlimAppLoginManager($capsule, $sessionHelper);
    },

    SlimAppMiddlewareLocale::class => function (ContainerInterface $c, SymfonyTranslator $translator) {
        $settings = $c->get('settings');
        $localeSettings = $settings['locale'];

        $localeMiddleware = new SlimAppMiddlewareLocale($translator, $localeSettings);

        return $localeMiddleware;
    }
];
