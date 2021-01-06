<?php

namespace horstoeko\slimapp\console\helper;

use horstoeko\slimapp\application\SlimApp;
use Symfony\Component\Console\Helper\Helper;

class SlimAppConsoleApplicationHelper extends Helper
{
    /**
     * SlimApp Application
     *
     * @var SlimApp
     */
    protected $slimApp;

    /**
     * Constructor.
     *
     * @param SlimApp $app
     */
    public function __construct(SlimApp $slimApp)
    {
        $this->slimApp = $slimApp;
    }

    /**
     * Retrieves SlimApp application instance.
     *
     * @return SlimApp
     */
    public function getSlimAppApplication()
    {
        return $this->slimApp;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'slimappapplication';
    }
}
