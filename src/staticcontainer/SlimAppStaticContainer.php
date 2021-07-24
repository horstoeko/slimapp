<?php

declare(strict_types=1);

namespace horstoeko\slimapp\staticcontainer;

use horstoeko\slimapp\event\SlimAppAfterInitApplicationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlimAppStaticContainer implements EventSubscriberInterface
{
    /**
     * The container reference
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected static $container;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SlimAppAfterInitApplicationEvent::class => 'onAfterInitApplication',
        ];
    }

    /**
     * Event subscriber for the event when the application
     * is fully initialized
     *
     * @param SlimAppAfterInitApplicationEvent $event
     * @return void
     */
    public function onAfterInitApplication(SlimAppAfterInitApplicationEvent $event): void
    {
        static::$container = $event->getContainer();
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public static function get(string $id)
    {
        return static::$container->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public static function has(string $id)
    {
        return static::$container->has($id);
    }
}
