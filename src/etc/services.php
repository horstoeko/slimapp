<?php

declare(strict_types=1);

use horstoeko\slimapp\middleware\SlimAppMiddlewareBasicAuth;
use horstoeko\slimapp\middleware\SlimAppMiddlewareIpAddress;
use horstoeko\slimapp\middleware\SlimAppMiddlewareLocale;
use horstoeko\slimapp\middleware\SlimAppMiddlewareRestrictedRouteAdmin;
use horstoeko\slimapp\middleware\SlimAppMiddlewareRestrictedRouteLight;
use horstoeko\slimapp\security\SlimAppLoginManager;
use horstoeko\slimapp\system\SlimAppDirectories;
use horstoeko\slimapp\twig\SlimAppTwig;
use horstoeko\slimapp\twig\SlimAppTwigApcCache;
use horstoeko\slimapp\twig\SlimAppTwigApcuCache;
use horstoeko\slimapp\twig\SlimAppTwigRoutingExtension;
use horstoeko\slimapp\twig\SlimAppTwigSecurityExtension;
use horstoeko\slimapp\validation\SlimAppValidator;
use horstoeko\stringmanagement\PathUtils;
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher as IlluminateEventDispatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Middleware\Session as SessionMiddleware;
use SlimSession\Helper as SessionHelper;
use Symfony\Bridge\Twig\Extension\TranslationExtension as SymfonyTwigBridgeTranslationExtension;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\Translation\Loader\ArrayLoader as SymfonyTranslatorArrayLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader as SymfonyTranslatorPhpFileLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Twig\Extension\DebugExtension as TwigDebugExtension;
use Twig\Extra\Html\HtmlExtension as TwigHtmlExtension;
use Twig\Extra\Intl\IntlExtension as TwigIntlExtension;
use Twig\Extra\Markdown\DefaultMarkdown as TwigDefaultMarkdown;
use Twig\Extra\Markdown\MarkdownExtension as TwigMarkdownExtension;
use Twig\Extra\Markdown\MarkdownRuntime as TwigMarkdownRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface as TwigRuntimeLoaderInterface;

return [
    SlimAppDirectories::class => function () {
        return new SlimAppDirectories();
    },

    LoggerInterface::class => function (SlimAppDirectories $directories, ContainerInterface $c) {
        $settings = $c->get('settings');
        $loggerSettings = $settings['logger'] ?? [];

        $logger = new Logger($loggerSettings['name']);
        $processor = new UidProcessor();
        $handler = new StreamHandler(PathUtils::combinePathWithFile($directories->gettemporarylogdirectory(), "app.log"), $loggerSettings['level']);

        $logger->pushProcessor($processor);
        $logger->pushHandler($handler);

        return $logger;
    },

    SessionMiddleware::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $sessionSettings = $settings['session'] ?? [];

        return new SessionMiddleware($sessionSettings);
    },

    SessionHelper::class => function () {
        return new SessionHelper();
    },

    SymfonyTranslator::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $translatorSettings = $settings['translator'] ?? [];

        $translator = new SymfonyTranslator($translatorSettings["defaultlanguagecode"]);
        $translator->setFallbackLocales([$translatorSettings["defaultlanguagecode"]]);
        $translator->addLoader('php', new SymfonyTranslatorPhpFileLoader());
        $translator->addLoader('array', new SymfonyTranslatorArrayLoader());

        return $translator;
    },

    SlimAppTwig::class => function (
        ContainerInterface $c,
        SymfonyTranslator $translator,
        SlimAppLoginManager $loginManager,
        Slim\App $app,
        SlimAppDirectories $directories
    ) {
        $settings = $c->get('settings');
        $twigSettings = $settings['twig'] ?? [];

        switch ($twigSettings["cachemode"] ?? 0) {
            case 1:
                $cache = $directories->gettemporarytwigdirectory();
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

        $dirCollection = [];

        if (is_dir($directories->getcustomtemplatesdirectory())) {
            $dirCollection["slimbaseapp"] = $directories->getvendortemplatesdirectory();
        }

        if (is_dir($directories->getvendortemplatesdirectory())) {
            $dirCollection["slimapp"] = $directories->getcustomtemplatesdirectory();
        }

        foreach ($twigSettings["additionaldirectories"] ?? [] as $additionaldirectoryns => $additionaldirectory) {
            if (is_dir($additionaldirectory)) {
                $dirCollection[$additionaldirectoryns] = $additionaldirectory;
            }
        }

        $view = new SlimAppTwig(
            $dirCollection,
            [
                'cache' => $cache,
                'strict_variables' => $twigSettings["strict_variables"] ?? false,
                'auto_reload' => $twigSettings["auto_reload"] ?? true,
                'debug' => $twigSettings["debug"] ?? false,
                'optimizations' => $twigSettings["optimizations"] ?? -1,
            ]
        );

        $view->addExtension(
            new TwigIntlExtension()
        );
        $view->addExtension(
            new TwigMarkdownExtension()
        );
        $view->addExtension(
            new TwigHtmlExtension()
        );
        $view->addExtension(
            new TwigDebugExtension()
        );
        $view->addExtension(
            new SymfonyTwigBridgeTranslationExtension($translator)
        );
        $view->addExtension(
            new SlimAppTwigSecurityExtension($loginManager)
        );
        $view->addExtension(
            new SlimAppTwigRoutingExtension(
                $app->getRouteCollector(),
                $app->getRouteCollector()->getRouteParser()
            )
        );

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

    IlluminateContainer::class => function () {
        return new IlluminateContainer();
    },

    IlluminateEventDispatcher::class => function (ContainerInterface $c) {
        return new IlluminateEventDispatcher($c->get(IlluminateContainer::class));
    },

    Capsule::class => function (ContainerInterface $c, LoggerInterface $logger) {
        $settings = $c->get('settings');
        $dbSettings = $settings['db'] ?? [];
        $dbObservers = $dbSettings['observers'] ?? [];
        $dbLogEnabled = $dbSettings['logenabled'] ?? false;
        $dbExtraConnections = $dbSettings['extraconnections'] ?? [];

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

        foreach ($dbExtraConnections as $dbExtraConnectionName => $dbExtraConnectionConfig) {
            $capsule->addConnection($dbExtraConnectionConfig, $dbExtraConnectionName);
        }

        $capsule->setEventDispatcher($c->get(IlluminateEventDispatcher::class));
        $capsule->setAsGlobal();

        if ($dbLogEnabled === true) {
            $capsule->getConnection()->setEventDispatcher($c->get(IlluminateEventDispatcher::class));
            $capsule->getConnection()->listen(function ($query) use ($logger) {
                $logger->debug(
                    "[PRIMCONN] Time: " . number_format($query->time ?? 0, 5, ",", ".") . ", SQL: " . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . PHP_EOL
                );
            });

            foreach ($dbExtraConnections as $dbExtraConnectionName => $dbExtraConnectionConfig) {
                $capsule->getConnection($dbExtraConnectionName)->setEventDispatcher($c->get(IlluminateEventDispatcher::class));
                $capsule->getConnection($dbExtraConnectionName)->listen(function ($query) use ($logger, $dbExtraConnectionName) {
                    $logger->debug(
                        "[" . $dbExtraConnectionName . "] Time: " . number_format($query->time ?? 0, 5, ",", ".") . ", SQL: " . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . PHP_EOL
                    );
                });
            }
        }

        $capsule->bootEloquent();

        foreach ($dbObservers as $modelClass => $dbObserverClass) {
            $modelClass::observe($c->get($dbObserverClass));
        }

        return $capsule;
    },

    SlimAppLoginManager::class => function (Capsule $capsule, SessionHelper $sessionHelper) {
        return new SlimAppLoginManager($capsule, $sessionHelper);
    },

    SlimAppValidator::class => function (ContainerInterface $c, SymfonyTranslator $translator, SessionHelper $sessionHelper) {
        $settings = $c->get('settings');
        $validatorSettings = $settings['validator'] ?? [];

        return new SlimAppValidator($translator, $sessionHelper, $validatorSettings);
    },

    SlimAppMiddlewareLocale::class => function (ContainerInterface $c, SymfonyTranslator $translator, SlimAppDirectories $directories) {
        $settings = $c->get('settings');
        $localeSettings = $settings['locale'] ?? [];

        $localeMiddleware = new SlimAppMiddlewareLocale($translator, $directories, $localeSettings);

        return $localeMiddleware;
    },

    SlimAppMiddlewareBasicAuth::class => function (
        ContainerInterface $c,
        SlimAppLoginManager $loginManager,
        \Slim\App $app
    ) {
        $settings = $c->get('settings');
        $basicAuthSettings = $settings['basicauth'] ?? [];

        $basicAuthMiddleware = new SlimAppMiddlewareBasicAuth(
            $loginManager,
            $app->getResponseFactory(),
            $basicAuthSettings
        );

        return $basicAuthMiddleware;
    },

    SlimAppMiddlewareIpAddress::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $ipAddrSettings = $settings['ipaddr'] ?? [];

        $ipAddrMiddleware = new SlimAppMiddlewareIpAddress($ipAddrSettings);

        return $ipAddrMiddleware;
    },

    SlimAppMiddlewareRestrictedRouteAdmin::class => function (
        ContainerInterface $c,
        SlimAppLoginManager $loginManager,
        \Slim\App $app
    ) {
        $settings = $c->get('settings');
        $restrictedRouteSettings = $settings['restrictedroute.admin'] ?? [];

        $restrictedRoute = new SlimAppMiddlewareRestrictedRouteAdmin(
            $loginManager,
            $app->getResponseFactory(),
            $restrictedRouteSettings
        );

        return $restrictedRoute;
    },

    SlimAppMiddlewareRestrictedRouteLight::class => function (
        ContainerInterface $c,
        SlimAppLoginManager $loginManager,
        \Slim\App $app
    ) {
        $settings = $c->get('settings');
        $restrictedRouteSettings = $settings['restrictedroute.light'] ?? [];

        $restrictedRoute = new SlimAppMiddlewareRestrictedRouteLight(
            $loginManager,
            $app->getResponseFactory(),
            $restrictedRouteSettings
        );

        return $restrictedRoute;
    },

    SymfonyEventDispatcher::class => function () {
        return new SymfonyEventDispatcher();
    },

    PHPMailer::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $mailerSettings = $settings['mail'] ?? [];

        $phpMail = new PHPMailer(false);

        $phpMail->isSMTP();
        $phpMail->Host = $mailerSettings["smtphost"];
        $phpMail->Port = $mailerSettings["smtpport"];
        $phpMail->SMTPAuth = $mailerSettings["smtpauth"];
        $phpMail->Username = $mailerSettings["smtpuser"];
        $phpMail->Password = $mailerSettings["smtppasswd"];
        $phpMail->SMTPSecure = $mailerSettings["smtpsecure"];
        $phpMail->SMTPOptions = $mailerSettings["smtpoptions"];

        return $phpMail;
    },
];
