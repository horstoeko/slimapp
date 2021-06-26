<?php

declare(strict_types=1);

namespace horstoeko\slimapp\console\command;

use Exception;
use horstoeko\slimapp\console\command\SlimAppConsoleBasicCommand;
use horstoeko\slimapp\exception\SlimAppValidationException;
use horstoeko\slimapp\security\SlimAppLoginManager;
use horstoeko\slimapp\system\SlimAppDirectories;
use horstoeko\stringmanagement\FileUtils;
use horstoeko\stringmanagement\PathUtils;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

class SlimAppConsoleBasicExtCommand extends SlimAppConsoleBasicCommand
{
    /**
     * @var SymfonyStyle
     */
    protected $ui;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Capsule
     */
    protected $capsule;

    /**
     * @var SlimAppLoginManager
     */
    protected $loginManager;

    /**
     * @var Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var horstoeko\slimapp\system\SlimAppDirectories
     */
    protected $directories;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @inheritDoc
     */
    protected function doexecute(InputInterface $input, OutputInterface $output)
    {
        $this->setIO($input, $output);
        $this->startSession();
        $this->init();
        $this->showWelcome();

        try {
            $executionResult = $this->doexecutecommand();
        } catch (SlimAppValidationException $e) {
            $this->ui->error($e->getMessage());
            $validator = $e->getValidator();
            $validationErrors = $validator->GetErrors();
            foreach ($validationErrors as $validationError) {
                foreach ($validationError as $message) {
                    $this->ui->writeln(sprintf(" - %s", $message));
                }
            }
            $executionResult = $e->getCode() ? (int)$e->getCode() : 1;
        } catch (Exception $e) {
            $this->ui->error($e->getMessage());
            $executionResult = $e->getCode() ? (int)$e->getCode() : 1;
        }

        $this->ui->newLine();
        $this->closeSession();

        return $executionResult;
    }

    /**
     * Execute command in a safe way
     *
     * @return integer
     */
    protected function doexecutecommand(): int
    {
        return 0;
    }

    protected function setIO(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
    }

    protected function startSession(): void
    {
        session_start();
    }

    protected function closeSession(): void
    {
        session_destroy();
    }

    protected function showWelcome(): void
    {
        $this->ui->title("LightDMS Console v1.0");
    }

    protected function init(): void
    {
        $this->ui = new SymfonyStyle($this->input, $this->output);
        $this->capsule = $this->slimAppContainer->get(Capsule::class);
        $this->loginManager = $this->slimAppContainer->get(SlimAppLoginManager::class);
        $this->translator = $this->slimAppContainer->get(SymfonyTranslator::class);
        $this->directories = $this->slimAppContainer->get(SlimAppDirectories::class);
        $this->logger = $this->slimAppContainer->get(LoggerInterface::class);

        $this->initTranslator();
    }

    protected function initTranslator(): void
    {
        $language = "en_GB";

        $translationfileName = FileUtils::combineFilenameWithFileextension($language, "php");
        $translationfiles = [
            [PathUtils::combinePathWithFile($this->directories->getvendorsettingsdirectory(), $translationfileName), "slimbaseapp"],
            [PathUtils::combinePathWithFile($this->directories->getcustomsettingsdirectory(), $translationfileName), "slimapp"],
        ];

        $this->translator->setLocale($language);

        foreach ($translationfiles as $translationfile) {
            if (!file_exists($translationfile[0])) {
                continue;
            }

            $this->translator->addResource(
                'php',
                $translationfile[0],
                $language,
                $translationfile[1]
            );
        }
    }

    protected function writelnNormal($message): void
    {
        $this->ui->writeln($message, OutputInterface::VERBOSITY_NORMAL);
    }

    protected function writelnVerbose($message): void
    {
        $this->ui->writeln($message, OutputInterface::VERBOSITY_VERBOSE);
    }

    protected function writelnVeryVerbose($message): void
    {
        $this->ui->writeln($message, OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    protected function writelnDebug($message): void
    {
        $this->ui->writeln($message, OutputInterface::VERBOSITY_DEBUG);
    }
}
