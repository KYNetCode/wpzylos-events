<?php

declare(strict_types=1);

namespace WPZylos\Framework\Events;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Listener provider for event dispatcher.
 *
 * Manages event listener registration and retrieval.
 *
 * @package WPZylos\Framework\Events
 */
class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<string, array<int, array<callable>>> Listeners by event class and priority
     */
    private array $listeners = [];

    /**
     * Add a listener for an event type.
     *
     * @param string $eventClass Fully qualified event class name
     * @param callable $listener Listener callback
     * @param int $priority Priority (lower = earlier, default: 10)
     *
     * @return static
     */
    public function addListener(string $eventClass, callable $listener, int $priority = 10): static
    {
        $this->listeners[ $eventClass ][ $priority ][] = $listener;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param object $event Event object
     *
     * @return iterable<callable> Listeners for the event
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);
        $listeners  = [];

        // Get listeners for the exact class
        if (isset($this->listeners[ $eventClass ])) {
            $listeners = $this->collectListeners($this->listeners[ $eventClass ]);
        }

        // Also get listeners for parent classes and interfaces
        foreach (class_parents($event) as $parent) {
            if (isset($this->listeners[ $parent ])) {
                $listeners = array_merge(
                    $listeners,
                    $this->collectListeners($this->listeners[ $parent ])
                );
            }
        }

        foreach (class_implements($event) as $interface) {
            if (isset($this->listeners[ $interface ])) {
                $listeners = array_merge(
                    $listeners,
                    $this->collectListeners($this->listeners[ $interface ])
                );
            }
        }

        return $listeners;
    }

    /**
     * Collect listeners sorted by priority.
     *
     * @param array<int, array<callable>> $prioritizedListeners
     *
     * @return array<callable>
     */
    private function collectListeners(array $prioritizedListeners): array
    {
        ksort($prioritizedListeners);

        $collected = [];
        foreach ($prioritizedListeners as $listeners) {
            foreach ($listeners as $listener) {
                $collected[] = $listener;
            }
        }

        return $collected;
    }

    /**
     * Remove all listeners for an event type.
     *
     * @param string $eventClass Event class name
     *
     * @return static
     */
    public function clearListeners(string $eventClass): static
    {
        unset($this->listeners[ $eventClass ]);

        return $this;
    }

    /**
     * Check if any listeners are registered for an event type.
     *
     * @param string $eventClass Event class name
     *
     * @return bool
     */
    public function hasListeners(string $eventClass): bool
    {
        return isset($this->listeners[ $eventClass ]) && ! empty($this->listeners[ $eventClass ]);
    }
}
