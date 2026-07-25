<?php

declare(strict_types=1);

namespace WPZylos\Framework\Events;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base class for stoppable events.
 *
 * Extend this class for events that can have their propagation stopped.
 *
 * @package WPZylos\Framework\Events
 */
abstract class StoppableEvent implements StoppableEventInterface
{
    /**
     * @var bool Whether propagation is stopped
     */
    private bool $propagationStopped = false;

    /**
     * Stop event propagation.
     *
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
