<?php

declare(strict_types=1);

namespace horstoeko\slimapp\console\command;

use horstoeko\stringmanagement\StringUtils;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class SlimAppConsoleUserTokenCommand extends SlimAppConsoleBasicExtCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('slimapp:users:token')
            ->setDescription('Create a token for an existing user login')
            ->setHelp("Create a token for an existing user login")
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputArgument("username", InputArgument::REQUIRED, "The user to authenticate at host"),
                        new InputOption("overwrite", "o", InputOption::VALUE_NONE, "Overwrite an existing token"),
                    ]
                )
            );
    }

    /**
     * @inheritDoc
     */
    protected function doexecutecommand(): int
    {
        // Init variables

        $username = (string)$this->input->getArgument('username');
        $overwrite = (bool)$this->input->getOption('overwrite');

        // Check

        if (StringUtils::stringIsNullOrEmpty($username)) {
            $this->writelnNormal("<error>The username must not be empty</error>");
            return 1;
        }

        // Create the user

        $this->writelnNormal(sprintf("Now creating token for user %s...", $username));

        $userId = $this->loginManager->createUserToken($username, $overwrite);

        // Finished

        if ($userId == -1) {
            $this->writelnNormal(sprintf('<error>User %s was not found</error>', $username));
        } else if ($userId == -2) {
            $this->writelnNormal(sprintf('<error>Token for User %s was not generated, because the is already one. You can use the -o switch</error>', $username));
        } else {
            $this->writelnNormal(sprintf("Token for User with id %s", $userId));
        }

        return 0;
    }
}
