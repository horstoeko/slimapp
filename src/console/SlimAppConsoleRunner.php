<?php

namespace horstoeko\slimapp\console;

use horstoeko\slimapp\application\SlimApp;
use horstoeko\slimapp\console\command\SlimAppConsoleDirectoriesListCommand;
use horstoeko\slimapp\console\command\SlimAppConsoleRoutesListCommand;
use horstoeko\slimapp\console\command\SlimAppConsoleUserCreateCommand;
use horstoeko\slimapp\console\command\SlimAppConsoleUserDeleteCommand;
use horstoeko\slimapp\console\command\SlimAppConsoleUserModifyCommand;
use horstoeko\slimapp\console\command\SlimAppConsoleUserTokenCommand;
use horstoeko\slimapp\console\helper\SlimAppConsoleApplicationHelper;
use horstoeko\slimapp\console\helper\SlimAppConsoleContainerHelper;
use horstoeko\slimapp\console\helper\SlimAppConsoleCoreApplicationHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

final class SlimAppConsoleRunner
{
    /**
     * Create a Symfony Console HelperSet
     *
     * @return HelperSet
     */
    public static function createHelperSet(): HelperSet
    {
        $slimApp = SlimApp::createApplication();
        $slimAppContainer = $slimApp->getContainer();
        $slimAppCoreApp = $slimApp->getCoreApplication();

        return new HelperSet(
            [
                'slimApp' => new SlimAppConsoleApplicationHelper($slimApp),
                'slimAppContainer' => new SlimAppConsoleContainerHelper($slimAppContainer),
                'slimAppCoreApp' => new SlimAppConsoleCoreApplicationHelper($slimAppCoreApp),
            ]
        );
    }

    /**
     * Runs console with the given helper set.
     *
     * @return void
     */
    public static function run(): void
    {
        $cli = self::createApplication(self::createHelperSet(), []);
        $cli->run();
    }

    /**
     * Creates a console application with the given helperset and
     * optional commands.
     *
     * @param \Symfony\Component\Console\Helper\HelperSet $helperSet
     * @param array                                       $commands
     *
     * @return \Symfony\Component\Console\Application
     * @throws OutOfBoundsException
     */
    public static function createApplication(HelperSet $helperSet, array $commands = []): Application
    {
        $cli = new Application('SlimApp Command Line Interface', '1.0.0');
        $cli->setCatchExceptions(true);
        $cli->setHelperSet($helperSet);
        self::addCommands($cli);
        $cli->addCommands($commands);

        return $cli;
    }

    /**
     * @param Application $cli
     *
     * @return void
     */
    public static function addCommands(Application $cli): void
    {
        $cli->addCommands(
            [
                new SlimAppConsoleRoutesListCommand(),
                new SlimAppConsoleDirectoriesListCommand(),
                new SlimAppConsoleUserCreateCommand(),
                new SlimAppConsoleUserModifyCommand(),
                new SlimAppConsoleUserDeleteCommand(),
                new SlimAppConsoleUserTokenCommand(),
            ]
        );

        /**
         * @var SlimAppConsoleContainerHelper
         */
        $slimAppContainerHelper = $cli->getHelperSet()->get('slimAppContainer');

        /**
         * @var ContainerInterface
         */
        $container = $slimAppContainerHelper->getSlimAppContainer();

        /**
         * @var array
         */
        $consoleCommands = $container->get('consolecommands');

        foreach ($consoleCommands as $consoleCommand) {
            $cli->addCommands([$consoleCommand]);
        }
    }
}
