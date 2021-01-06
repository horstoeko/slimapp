<?php

namespace horstoeko\slimapp\console\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SlimAppConsoleBasicCommand extends Command
{
    /**
     * SlimApp
     *
     * @var \horstoeko\slimapp\application\SlimApp
     */
    protected $slimApp;

    /**
     * SlimApp Core Application
     *
     * @var \Slim\App
     */
    protected $slimAppCoreApplication;

    /**
     * SlimApp Dependency Container
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $slimAppContainer;

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->slimApp = $this->getHelper('slimApp')->getSlimAppApplication();
        $this->slimAppCoreApplication = $this->getHelper('slimAppCoreApp')->getSlimAppCoreApplication();
        $this->slimAppContainer = $this->getHelper('slimAppContainer')->getSlimAppContainer();

        return $this->doexecute($input, $output);
    }

    /**
     * Execution method for SlimApp commands. This must be overriden in
     * derived classes
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function doexecute(InputInterface $input, OutputInterface $output)
    {
        throw new LogicException('You must override the execute() method in the concrete command class.');
    }
}
