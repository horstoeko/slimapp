<?php

declare(strict_types=1);

namespace horstoeko\slimapp\console\command;

use horstoeko\stringmanagement\StringUtils;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class SlimAppConsoleUserCreateCommand extends SlimAppConsoleBasicExtCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('slimapp:users:create')
            ->setDescription('Create a new user login')
            ->setHelp("Create a new user login")
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputArgument("username", InputArgument::REQUIRED, "The user to authenticate at host"),
                        new InputOption("password", "p", InputOption::VALUE_REQUIRED, "The password to authenticate at host"),
                        new InputOption("firstname", "f", InputOption::VALUE_REQUIRED, "The first name of the user"),
                        new InputOption("lastname", "l", InputOption::VALUE_REQUIRED, "The last name of the user"),
                        new InputOption("email", "e", InputOption::VALUE_REQUIRED, "The e-mail address of the user"),
                        new InputOption("token", "t", InputOption::VALUE_OPTIONAL, "The user specific token for API access"),
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

        if (StringUtils::stringIsNullOrEmpty($password)) {
            $this->writelnNormal("<error>The password must not be empty</error>");
            return 1;
        }

        if (StringUtils::stringIsNullOrEmpty($firstname)) {
            $this->writelnNormal("<error>The first name must not be empty</error>");
            return 1;
        }

        if (StringUtils::stringIsNullOrEmpty($lastname)) {
            $this->writelnNormal("<error>The last name must not be empty</error>");
            return 1;
        }

        if (StringUtils::stringIsNullOrEmpty($email)) {
            $this->writelnNormal("<error>The email address must not be empty</error>");
            return 1;
        }

        // Create the user

        $this->writelnNormal(sprintf("Now creating user %s...", $username));

        $userId = $this->loginManager->createUser(
            $username,
            $password,
            $firstname,
            $lastname,
            $email,
            $token
        );

        // Finished

        $this->writelnNormal(sprintf("User created with id %s", $userId));

        return 0;
    }
}
