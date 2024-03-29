<?php

declare(strict_types=1);

namespace horstoeko\slimapp\console\command;

use horstoeko\stringmanagement\StringUtils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

class SlimAppConsoleUserModifyCommand extends SlimAppConsoleBasicExtCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('slimapp:users:modify')
            ->setDescription('Modify an existing user login')
            ->setHelp("Modify an existing user login")
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputArgument("username", InputArgument::REQUIRED, "The user to authenticate at host"),
                        new InputOption("password", "p", InputOption::VALUE_REQUIRED, "The password to authenticate at host"),
                        new InputOption("firstname", "f", InputOption::VALUE_REQUIRED, "The first name of the user"),
                        new InputOption("lastname", "l", InputOption::VALUE_REQUIRED, "The last name of the user"),
                        new InputOption("email", "e", InputOption::VALUE_REQUIRED, "The e-mail address of the user"),
                        new InputOption("token", "t", InputOption::VALUE_REQUIRED, "The user specific token for API access"),
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
        $password = (string)$this->input->getOption('password');
        $firstname = (string)$this->input->getOption('firstname');
        $lastname = (string)$this->input->getOption('lastname');
        $email = (string)$this->input->getOption('email');
        $token = (string)$this->input->getOption('token');

        // Check

        if (StringUtils::stringIsNullOrEmpty($username)) {
            $this->writelnNormal("<error>The username must not be empty</error>");
            return 1;
        }

        // Create the user

        $this->writelnNormal(sprintf("Now updating user %s...", $username));

        $userId = $this->loginManager->modifyUser(
            $username,
            $password,
            $firstname,
            $lastname,
            $email,
            $token
        );

        // Finished

        if ($userId == -1) {
            $this->writelnNormal(sprintf('<error>User %s was not found</error>', $username));
        } else {
            $this->writelnNormal(sprintf("User with id %s was updated", $userId));
        }

        return 0;
    }
}
