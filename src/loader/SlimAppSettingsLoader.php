<?php

declare(strict_types=1);

namespace horstoeko\slimapp\loader;

use DI\ContainerBuilder;
use horstoeko\stringmanagement\PathUtils;

class SlimAppSettingsLoader extends SlimAppBaseLoader
{
    /**
     * @var \DI\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @inheritDoc
     */
    public function __construct(ContainerBuilder $containerBuilder)
    {
        parent::__construct();

        $this->containerBuilder = $containerBuilder;
    }

    /**
     * @inheritDoc
     */
    protected function getFiles(): array
    {
        return [
            PathUtils::combinePathWithFile($this->directories->getvendorsettingsdirectory(), "settings.php"),
            PathUtils::combinePathWithFile($this->directories->getcustomsettingsdirectory(), "settings.php"),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function onAfterLoad(array $content): void
    {
        $this->containerBuilder->addDefinitions(
            [
                'settings' => $content,
                'config' => $content,
            ]
        );
    }
}
