<?php

declare(strict_types=1);

namespace WPZylos\Framework\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * PSR-14 compliant event dispatcher.
 *
 * Dispatches events to registered listeners via the listener provider.
 *
 * @package WPZylos\Framework\Events
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var ListenerProviderInterface Listener provider
     */
    private ListenerProviderInterface $listenerProvider;

    /**
     * Create an event dispatcher.
     *
     * @param ListenerProviderInterface $listenerProvider Listener provider
     */
    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;
    }

    /**
     * Dispatch an event to all registered listeners.
     *
     * @param object $event Event to dispatch
     * @return object The passed event after all listeners have processed it
     */
    public function dispatch(object $event): object
    {
        $listeners = $this->listenerProvider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            // Check if propagation should stop
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }

            $listener($event);
        }

        return $event;
    }
}
