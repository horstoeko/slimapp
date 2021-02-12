<?php

declare(strict_types=1);

namespace horstoeko\slimapp\application;

use Slim\App;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Psr\Container\ContainerInterface;
use horstoeko\slimapp\loader\SlimAppRouteLoader;
use horstoeko\slimapp\loader\SlimAppServiceLoader;
use horstoeko\slimapp\loader\SlimAppSettingsLoader;
use horstoeko\slimapp\loader\SlimAppMiddlewareLoader;
use horstoeko\slimapp\loader\SlimAppConsoleCommandsLoader;
use horstoeko\slimapp\loader\SlimAppEventSubscriberLoader;

class SlimApp
{
    /**
     * @var \DI\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * Runs the application
     *
     * @return void
     */
    public static function run(): void
    {
        $slimApplication = self::createApplication();
        $slimApplication->runApplication();
    }

    /**
     * Prepare the application. This is used in the console application
     *
     * @return SlimApp
     */
    public static function createApplication(): SlimApp
    {
        $slimApplication = new self();
        $slimApplication->initContainerBuilder();
        $slimApplication->initSettings();
        $slimApplication->initConsoleCommands();
        $slimApplication->initServices();
        $slimApplication->initSlimmApp();
        $slimApplication->initEventSubscribers();
        $slimApplication->initMiddlewares();
        $slimApplication->initRoutes();
        $slimApplication->initSystemMiddlewares();

        return $slimApplication;
    }

    /**
     * Return the created container. This is used in the
     * console application
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Return the created Slim Application instance. This is used
     * in the console application
     *
     * @return App
     */
    public function getCoreApplication(): App
    {
        return $this->app;
    }

    /**
     * Internal initialize the DI container builder
     *
     * @return void
     */
    public function initContainerBuilder(): void
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * Creates the "real" slim application
     *
     * @return void
     */
    public function initSlimmApp(): void
    {
        $this->container = $this->containerBuilder->build();

        AppFactory::setContainer($this->container);

        $this->app = AppFactory::create();

        $this->container->set(App::class, $this->app);
        $this->container->set(SlimApp::class, $this);
    }

    /**
     * Initialize global settings
     *
     * @return void
     */
    public function initSettings(): void
    {
        $loader = new SlimAppSettingsLoader($this->containerBuilder);
        $loader->load();
    }

    /**
     * Initialize all application services
     *
     * @return void
     */
    public function initServices(): void
    {
        $loader = new SlimAppServiceLoader($this->containerBuilder);
        $loader->load();
    }

    /**
     * Initialize all event subscribers
     *
     * @return void
     */
    public function initEventSubscribers(): void
    {
        $loader = new SlimAppEventSubscriberLoader($this->container);
        $loader->load();
    }

    /**
     * Initialize all application middlewares
     *
     * @return void
     */
    public function initMiddlewares(): void
    {
        $loader = new SlimAppMiddlewareLoader($this->app);
        $loader->load();
    }

    /**
     * Initialize all routes
     *
     * @return void
     */
    public function initRoutes(): void
    {
        $loader = new SlimAppRouteLoader($this->app);
        $loader->load();
    }

    /**
     * Initialize all by the application needed base middlewares
     *
     * @return void
     */
    public function initSystemMiddlewares(): void
    {
        $displayErrorDetails = $this->container->get('settings')['displayErrorDetails'] ?? false;
        $logErrors = $this->container->get('settings')['logerrors'] ?? true;
        $logErrorDetails = $this->container->get('settings')['logerrordetails'] ?? true;
        $errorHandlers = $this->container->get('settings')['errorhandlers'] ?? [];
        $logger = $this->container->get(LoggerInterface::class);

        $bodyParsingMiddleware = $this->app->addBodyParsingMiddleware();
        $routingMiddleware = $this->app->addRoutingMiddleware();
        $errorMiddleware = $this->app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails, $logger);

        foreach ($errorHandlers as $errorHandlerExceptionClass => $errorHandlerClass) {
            $errorMiddleware->setErrorHandler($errorHandlerExceptionClass, $errorHandlerClass);
        }
    }

    /**
     * Initialize commands for the console application
     *
     * @return void
     */
    public function initConsoleCommands(): void
    {
        $loader = new SlimAppConsoleCommandsLoader($this->containerBuilder);
        $loader->load();
    }

    /**
     * Runs the "real" slim application
     *
     * @return void
     */
    public function runApplication(): void
    {
        $this->app->run();
    }
}
