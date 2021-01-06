<?php

namespace horstoeko\slimapp\console\command;

use horstoeko\slimapp\system\SlimAppDirectories;
use horstoeko\stringmanagement\StringUtils;
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
        $directories = new SlimAppDirectories;

        $table->setHeaders(['Which', 'Path', 'Exists']);
        $table->addRow(
            [
                "Root",
                $directories->getrootdirectory(),
                $this->dirExistsHumanReadable($directories->getrootdirectory())
            ]
        );
        $table->addRow(
            [
                "Document Root",
                $directories->getdocumentrootdirectory(),
                $this->dirExistsHumanReadable($directories->getdocumentrootdirectory())
            ]
        );
        $table->addRow(
            [
                "Sub-Directory",
                $directories->getsubdirname(),
                $this->dirExistsHumanReadable($directories->getsubdirname())
            ]
        );
        $table->addRow(
            [
                "System Base Directory",
                $directories->getvendorbasedirectory(),
                $this->dirExistsHumanReadable($directories->getvendorbasedirectory())
            ]
        );
        $table->addRow(
            [
                "Custom Base Directory",
                $directories->getcustombasedirectory(),
                $this->dirExistsHumanReadable($directories->getcustombasedirectory())
            ]
        );
        $table->addRow(
            [
                "System Directory",
                $directories->getvendordirectory(),
                $this->dirExistsHumanReadable($directories->getvendordirectory())
            ]
        );
        $table->addRow(
            [
                "Custom Directory",
                $directories->getcustomdirectory(),
                $this->dirExistsHumanReadable($directories->getcustomdirectory())
            ]
        );
        $table->addRow(
            [
                "System Settings directory",
                $directories->getvendorsettingsdirectory(),
                $this->dirExistsHumanReadable($directories->getvendorsettingsdirectory())
            ]
        );
        $table->addRow(
            [
                "Custom Settings directory",
                $directories->getcustomsettingsdirectory(),
                $this->dirExistsHumanReadable($directories->getcustomsettingsdirectory())
            ]
        );
        $table->addRow(
            [
                "System Twig Template directory",
                $directories->getvendortemplatesdirectory(),
                $this->dirExistsHumanReadable($directories->getvendortemplatesdirectory())
            ]
        );
        $table->addRow(
            [
                "Custom Twig Template directory",
                $directories->getcustomtemplatesdirectory(),
                $this->dirExistsHumanReadable($directories->getcustomtemplatesdirectory())
            ]
        );
        $table->addRow(
            [
                "Temporary Directory",
                $directories->gettemporarydirectory(),
                $this->dirExistsHumanReadable($directories->gettemporarydirectory())
            ]
        );
        $table->addRow(
            [
                "Temporary Twig Cache Directory",
                $directories->gettemporarytwigdirectory(),
                $this->dirExistsHumanReadable($directories->gettemporarytwigdirectory())
            ]
        );
        $table->addRow(
            [
                "Temporary Log Directory",
                $directories->gettemporarylogdirectory(),
                $this->dirExistsHumanReadable($directories->gettemporarylogdirectory())
            ]
        );
        $table->addRow(
            [
                "Temporary SSL Key Directory",
                $directories->gettemporarysslkeydirectory(),
                $this->dirExistsHumanReadable($directories->gettemporarysslkeydirectory())
            ]
        );

        $table->render();

        return 0;
    }

    private function dirExistsHumanReadable(string $directory): string
    {
        return StringUtils::stringIsNullOrEmpty($directory) ? "-" : (is_dir($directory) ? "Yes" : "No");
    }
}
