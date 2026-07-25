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
        $prioritized = [];

        if (isset($this->listeners[ $eventClass ])) {
            $prioritized = $this->mergePrioritized($prioritized, $this->listeners[ $eventClass ]);
        }

        foreach (class_parents($event) as $parent) {
            if (isset($this->listeners[ $parent ])) {
                $prioritized = $this->mergePrioritized($prioritized, $this->listeners[ $parent ]);
            }
        }

        foreach (class_implements($event) as $interface) {
            if (isset($this->listeners[ $interface ])) {
                $prioritized = $this->mergePrioritized($prioritized, $this->listeners[ $interface ]);
            }
        }

        return $this->collectListeners($prioritized);
    }

    /**
     * Merge listener buckets without losing global priority ordering.
     *
     * @param array<int, array<callable>> $target Existing buckets
     * @param array<int, array<callable>> $source Additional buckets
     *
     * @return array<int, array<callable>>
     */
    private function mergePrioritized(array $target, array $source): array
    {
        foreach ($source as $priority => $listeners) {
            foreach ($listeners as $listener) {
                $target[$priority][] = $listener;
            }
        }

        return $target;
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
     * Remove one listener without clearing other consumers.
     *
     * @param string   $eventClass Event class name
     * @param callable $listener   Listener to remove
     *
     * @return bool True when at least one registration was removed
     */
    public function removeListener(string $eventClass, callable $listener): bool
    {
        if (!isset($this->listeners[$eventClass])) {
            return false;
        }

        $removed = false;
        foreach ($this->listeners[$eventClass] as $priority => $listeners) {
            foreach ($listeners as $index => $registered) {
                if ($registered === $listener) {
                    unset($this->listeners[$eventClass][$priority][$index]);
                    $removed = true;
                }
            }

            if ($this->listeners[$eventClass][$priority] === []) {
                unset($this->listeners[$eventClass][$priority]);
            }
        }

        if ($this->listeners[$eventClass] === []) {
            unset($this->listeners[$eventClass]);
        }

        return $removed;
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
