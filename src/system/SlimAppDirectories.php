<?php

declare(strict_types=1);

namespace horstoeko\slimapp\system;

use horstoeko\stringmanagement\PathUtils;
use horstoeko\slimapp\system\SlimAppEnvironment;

class SlimAppDirectories
{
    /**
     * Document root
     *
     * @var string
     */
    private $documentRoot = "";

    /**
     * Subdirectory in document root (may be empty)
     *
     * @var string
     */
    private $subDirName = "";

    /**
     * Base directory
     *
     * @var string
     */
    private $dirBase = "";

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setup();
    }

    public function getrootdirectory(): string
    {
        return $this->dirBase;
    }

    public function getdocumentrootdirectory(): string
    {
        return $this->documentRoot;
    }

    public function getrelativerootdirectory(): string
    {
        return PathUtils::combineAllPaths(DIRECTORY_SEPARATOR, $this->subDirName);
    }

    public function getsubdirname(): string
    {
        return $this->subDirName;
    }

    public function getcustombasedirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($relative === true ? $this->subDirName : $this->dirBase);
    }

    public function getvendorbasedirectory($relative = false): string
    {
        return PathUtils::combineAllPaths(
            $relative === true ? $this->subDirName : $this->dirBase,
            "vendor",
            "horstoeko",
            "slimapp",
            "src"
        );
    }

    public function getcustomdirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->getcustombasedirectory(), "app");
    }

    public function getvendordirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->getvendorbasedirectory(), "baseapp");
    }

    public function gettemporarydirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->getcustombasedirectory(), "var");
    }

    public function gettemporarytwigdirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->gettemporarydirectory($relative), "twig");
    }

    public function gettemporarylogdirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->gettemporarydirectory($relative), "log");
    }

    public function gettemporarysslkeydirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->gettemporarydirectory($relative), "ssl");
    }

    public function getcustomsettingsdirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->getcustombasedirectory(), "etc");
    }

    public function getvendorsettingsdirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->getvendorbasedirectory($relative), "etc");
    }

    public function getcustomtemplatesdirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->getcustomdirectory($relative), "html");
    }

    public function getvendortemplatesdirectory($relative = false): string
    {
        return PathUtils::combineAllPaths($this->getvendordirectory($relative), "html");
    }

    /**
     * Internal function to setup required base directories
     *
     * @return void
     */
    private function setup(): void
    {
        if ($this->isCommandLineInterface()) {
            $this->documentRoot = realpath(__DIR__ . "/../../../../../");
            $this->subDirName = "";
            $this->dirBase = PathUtils::combineAllPaths($this->documentRoot, $this->subDirName);
        } else {
            $this->documentRoot = SlimAppEnvironment::env('DOCUMENT_ROOT');
            $this->subDirName = substr_replace(
                dirname(getenv('SCRIPT_FILENAME')) . DIRECTORY_SEPARATOR,
                "",
                0,
                strlen($this->documentRoot . DIRECTORY_SEPARATOR) - 1
            );
            $this->dirBase = PathUtils::combineAllPaths($this->documentRoot, $this->subDirName);
        }
    }

    /**
     * Returns true if the script is called from command line
     *
     * @return boolean
     */
    private function isCommandLineInterface(): bool
    {
        return (php_sapi_name() === 'cli');
    }
}
