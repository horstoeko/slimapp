<?php

namespace horstoeko\slimapp\console\command;

use \Symfony\Component\Console\Helper\Table;

class SlimAppConsoleRoutesListCommand extends SlimAppConsoleBasicExtCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('slimapp:routes:list')
            ->setDescription('List all routes')
            ->setHelp("List all routes");
    }

    /**
     * @inheritDoc
     */
    protected function doexecutecommand(): int
    {
        $table = new Table($this->output);
        $routes = $this->slimAppCoreApplication->getRouteCollector()->getRoutes();

        $table->setHeaders(['Method', 'Pattern', 'Name']);

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
