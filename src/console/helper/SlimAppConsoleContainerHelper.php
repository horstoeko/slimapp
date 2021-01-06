<?php

namespace horstoeko\slimapp\console\helper;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\Helper;

class SlimAppConsoleContainerHelper extends Helper
{
    /**
     * SlimApp Container
     *
     * @var ContainerInterface
     */
    protected $slimAppContainer;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->slimAppContainer = $c;
    }

    /**
     * Retrieves SlimApp container instance.
     *
     * @return ContainerInterface
     */
    public function getSlimAppContainer()
    {
        return $this->slimAppContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'slimappcontainer';
    }
}
