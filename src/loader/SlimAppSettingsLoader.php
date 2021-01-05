<?php

declare(strict_types=1);

namespace horstoeko\slimapp\loader;

use DI\ContainerBuilder;

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
        $this->containerBuilder = $containerBuilder;
    }

    /**
     * @inheritDoc
     */
    protected function getFiles(): array
    {
        return [
            __DIR__ . "/../etc/settings.php",
            __DIR__ . "/../../../../../etc/settings.php",
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
