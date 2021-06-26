<?php

declare(strict_types=1);

namespace horstoeko\slimapp\console\command;

use horstoeko\stringmanagement\StringUtils;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

class SlimAppConsoleUserDeleteCommand extends SlimAppConsoleBasicExtCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('slimapp:users:delete')
            ->setDescription('Delete a user login')
            ->setHelp("Delete a user login")
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption("username", "u", InputOption::VALUE_REQUIRED, "The user to authenticate at host"),
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

        $username = (string)$this->input->getOption('username');

        // Check

        if (StringUtils::stringIsNullOrEmpty($username)) {
            $this->writelnNormal("<error>The username must not be empty</error>");
            return 1;
        }

        // Create the user

        $this->writelnNormal(sprintf("Now deleting user %s...", $username));

        $userId = $this->loginManager->deleteUser($username);

        // Finished

        if ($userId == -1) {
            $this->writelnNormal(sprintf('<error>User %s was not found</error>', $username));
        } else {
            $this->writelnNormal(sprintf("User with id %s was deleted", $userId));
        }

        return 0;
    }
}
