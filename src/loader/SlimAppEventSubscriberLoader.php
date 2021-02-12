<?php

declare(strict_types=1);

namespace horstoeko\slimapp\loader;

use horstoeko\stringmanagement\PathUtils;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

class SlimAppEventSubscriberLoader extends SlimAppBaseLoader
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @inheritDoc
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    protected function getFiles(): array
    {
        return [
            PathUtils::combinePathWithFile($this->directories->getvendorsettingsdirectory(), "eventsubscribers.php"),
            PathUtils::combinePathWithFile($this->directories->getcustomsettingsdirectory(), "eventsubscribers.php"),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function onAfterLoad(array $content): void
    {
        /**
         * @var \Symfony\Component\EventDispatcher\EventDispatcher
         */
        $eventDispatcher = $this->container->get(SymfonyEventDispatcher::class);

        foreach ($content as $eventSubscriberClass) {
            $eventSubscriberInstance = $this->container->get($eventSubscriberClass);
            $eventDispatcher->addSubscriber($eventSubscriberInstance);
        }
    }
}
