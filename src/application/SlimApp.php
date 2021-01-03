<?php

declare(strict_types=1);

namespace horstoeko\slimapp\application;

use Slim\App;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use horstoeko\slimapp\loader\SlimAppRouteLoader;
use horstoeko\slimapp\loader\SlimAppServiceLoader;
use horstoeko\slimapp\loader\SlimAppSettingsLoader;
use horstoeko\slimapp\loader\SlimAppMiddlewareLoader;

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
        $slimApplication = new self();
        $slimApplication->initContainerBuilder();
        $slimApplication->initSettings();
        $slimApplication->initServices();
        $slimApplication->initSlimmApp();
        $slimApplication->initMiddlewares();
        $slimApplication->initRoutes();
        $slimApplication->initSystemMiddlewares();
        $slimApplication->runApplication();
    }

    /**
     * Internal initialize the DI container builder
     *
     * @return void
     */
    public function initContainerBuilder(): void
    {
        $this->containerBuilder = new ContainerBuilder();
        //$this->containerBuilder->enableCompilation(__DIR__ . '/../../var');
        //$this->containerBuilder->enableDefinitionCache();
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
        $logger = $this->container->get(LoggerInterface::class);

        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware($displayErrorDetails, true, true, $logger);
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
