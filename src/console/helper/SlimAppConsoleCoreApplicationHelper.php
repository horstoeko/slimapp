<?php

namespace horstoeko\slimapp\console\helper;

use Slim\App;
use Symfony\Component\Console\Helper\Helper;

class SlimAppConsoleCoreApplicationHelper extends Helper
{
    /**
     * SlimApp Core Application
     *
     * @var App
     */
    protected $slimAppCoreApplication;

    /**
     * Constructor.
     *
     * @param App $app
     */
    public function __construct(App $slimAppCoreApplication)
    {
        $this->slimAppCoreApplication = $slimAppCoreApplication;
    }

    /**
     * Retrieves SlimApp core application instance.
     *
     * @return App
     */
    public function getSlimAppCoreApplication()
    {
        return $this->slimAppCoreApplication;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'slimappcoreapplication';
    }
}
