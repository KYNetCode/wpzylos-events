<?php

declare(strict_types=1);

namespace WPZylos\Framework\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use WPZylos\Framework\Core\Contracts\ApplicationInterface;
use WPZylos\Framework\Core\ServiceProvider;

/**
 * Event service provider.
 *
 * Registers the event dispatcher and listener provider.
 *
 * @package WPZylos\Framework\Events
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(ApplicationInterface $app): void
    {
        parent::register($app);

        $this->singleton(ListenerProvider::class, fn() => new ListenerProvider());
        $this->singleton(ListenerProviderInterface::class, fn() => $this->make(ListenerProvider::class));

        $this->singleton(EventDispatcher::class, fn() => new EventDispatcher(
            $this->make(ListenerProvider::class)
        ));
        $this->singleton(EventDispatcherInterface::class, fn() => $this->make(EventDispatcher::class));
        $this->singleton('events', fn() => $this->make(EventDispatcher::class));
    }

    /**
     * Register an event subscriber.
     *
     * @param EventSubscriberInterface $subscriber
     * @return void
     */
    public function subscribe(EventSubscriberInterface $subscriber): void
    {
        $provider = $this->make(ListenerProvider::class);

        foreach ($subscriber::getSubscribedEvents() as $eventClass => $params) {
            if (is_string($params)) {
                $provider->addListener($eventClass, [$subscriber, $params]);
            } elseif (is_array($params)) {
                if (is_string($params[0])) {
                    $provider->addListener($eventClass, [$subscriber, $params[0]], $params[1] ?? 10);
                } else {
                    foreach ($params as $listener) {
                        $provider->addListener($eventClass, [$subscriber, $listener[0]], $listener[1] ?? 10);
                    }
                }
            }
        }
    }
}
