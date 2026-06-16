<?php

defined('ABSPATH') || exit;

declare(strict_types=1);

namespace WPZylos\Framework\Events\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Events\ListenerProvider;

/**
 * Tests for ListenerProvider.
 *
 * Note: ListenerProvider is already tested extensively in EventDispatcherTest.
 * These tests focus on the provider in isolation.
 */
class ListenerProviderTest extends TestCase
{
    public function testProviderIsInstantiable(): void
    {
        $provider = new ListenerProvider();
        $this->assertInstanceOf(ListenerProvider::class, $provider);
    }

    public function testAddListenerAndHasListeners(): void
    {
        $provider = new ListenerProvider();

        $this->assertFalse($provider->hasListeners(DummyEvent::class));

        $provider->addListener(DummyEvent::class, fn() => null);

        $this->assertTrue($provider->hasListeners(DummyEvent::class));
    }

    public function testClearListenersRemovesListeners(): void
    {
        $provider = new ListenerProvider();
        $provider->addListener(DummyEvent::class, fn() => null);

        $this->assertTrue($provider->hasListeners(DummyEvent::class));

        $provider->clearListeners(DummyEvent::class);

        $this->assertFalse($provider->hasListeners(DummyEvent::class));
    }

    public function testGetListenersForEventRespectsPriority(): void
    {
        $provider = new ListenerProvider();
        $order = [];

        $provider->addListener(DummyEvent::class, function () use (&$order) {
            $order[] = 'low';
        }, 20);

        $provider->addListener(DummyEvent::class, function () use (&$order) {
            $order[] = 'high';
        }, 5);

        $listeners = $provider->getListenersForEvent(new DummyEvent());

        // Execute the listeners to verify order
        foreach ($listeners as $listener) {
            $listener(new DummyEvent());
        }

        $this->assertSame(['high', 'low'], $order);
    }

    public function testClearListenersReturnsFluently(): void
    {
        $provider = new ListenerProvider();
        $result = $provider->clearListeners(DummyEvent::class);

        $this->assertSame($provider, $result);
    }
}

class DummyEvent
{
}
