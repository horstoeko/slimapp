<?php

namespace horstoeko\slimapp\console\command;

use \Symfony\Component\Console\Helper\Table;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class SlimAppConsoleShowDirectoriesCommand extends SlimAppConsoleBasicCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('slimapp:directories:list')
            ->setDescription('List all system directories')
            ->setHelp("List all system directories");
    }

    /**
     * @inheritDoc
     */
    protected function doexecute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $routes = $this->slimAppCoreApplication->getRouteCollector()->getRoutes();

        $table->setHeaders(['Which', 'Path']);

        foreach ($routes as $route) {
            $table->addRow([
                implode(",", $route->getMethods()),
                $route->getPattern(),
                $route->getName(),
            ]);
        }

        $table->render();

        return 0;
    }
}
