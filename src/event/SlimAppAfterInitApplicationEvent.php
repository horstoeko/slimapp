<?php

declare(strict_types=1);

namespace horstoeko\slimapp\event;

use horstoeko\slimapp\application\SlimApp;
use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

class SlimAppAfterInitApplicationEvent extends SymfonyEvent
{
    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * @var \horstoeko\slimapp\application\SlimApp
     */
    protected $slimApp;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param App $app
     * @param SlimApp $slimApp
     * @param ContainerInterface $container
     */
    public function __construct(App $app, SlimApp $slimApp, ContainerInterface $container)
    {
        $this->app = $app;
        $this->slimApp = $slimApp;
        $this->container = $container;
    }

    /**
     * Returns the core application
     *
     * @return App
     */
    public function getApp(): App
    {
        return $this->app;
    }

    /**
     * Returns the SlimApp application wrapper
     *
     * @return SlimApp
     */
    public function getSlimApp(): SlimApp
    {
        return $this->slimApp;
    }

    /**
     * Get the DI container
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
