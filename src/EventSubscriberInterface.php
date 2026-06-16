<?php

declare(strict_types=1);

namespace WPZylos\Framework\Events;

/**
 * Event subscriber interface.
 *
 * Subscribers can register multiple listeners at once.
 *
 * @package WPZylos\Framework\Events
 */
interface EventSubscriberInterface
{
    /**
     * Get the events this subscriber listens to.
     *
     * Return format:
     * [
     *     EventClass::class => 'methodName',
     *     EventClass::class => ['methodName', $priority],
     *     EventClass::class => [['method1', $priority1], ['method2', $priority2]],
     * ]
     *
     * @return array<string, string|array>
     */
    public static function getSubscribedEvents(): array;
}
