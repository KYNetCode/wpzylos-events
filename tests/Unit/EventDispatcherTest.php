<?php

defined('ABSPATH') || exit;

declare(strict_types=1);

namespace WPZylos\Framework\Events\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Events\EventDispatcher;
use WPZylos\Framework\Events\ListenerProvider;
use WPZylos\Framework\Events\StoppableEvent;

/**
 * Tests for EventDispatcher.
 */
class EventDispatcherTest extends TestCase
{
    public function testDispatchCallsListeners(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);

        $called = false;
        $provider->addListener(TestEvent::class, function (TestEvent $event) use (&$called) {
            $called = true;
            $event->data = 'modified';
        });

        $event = new TestEvent();
        $result = $dispatcher->dispatch($event);

        $this->assertTrue($called);
        $this->assertSame('modified', $result->data);
    }

    public function testDispatchRespectsStoppablePropagation(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);

        $callOrder = [];

        $provider->addListener(StoppableTestEvent::class, function (StoppableTestEvent $event) use (&$callOrder) {
            $callOrder[] = 1;
            $event->stopPropagation();
        }, 1);

        $provider->addListener(StoppableTestEvent::class, function (StoppableTestEvent $event) use (&$callOrder) {
            $callOrder[] = 2;
        }, 2);

        $event = new StoppableTestEvent();
        $dispatcher->dispatch($event);

        $this->assertSame([1], $callOrder);
    }

    public function testListenerProviderPriorityOrdering(): void
    {
        $provider = new ListenerProvider();

        $callOrder = [];

        $provider->addListener(TestEvent::class, function () use (&$callOrder) {
            $callOrder[] = 'low';
        }, 20);

        $provider->addListener(TestEvent::class, function () use (&$callOrder) {
            $callOrder[] = 'high';
        }, 5);

        $provider->addListener(TestEvent::class, function () use (&$callOrder) {
            $callOrder[] = 'medium';
        }, 10);

        $dispatcher = new EventDispatcher($provider);
        $dispatcher->dispatch(new TestEvent());

        $this->assertSame(['high', 'medium', 'low'], $callOrder);
    }

    public function testListenerProviderHasListeners(): void
    {
        $provider = new ListenerProvider();

        $this->assertFalse($provider->hasListeners(TestEvent::class));

        $provider->addListener(TestEvent::class, fn() => null);

        $this->assertTrue($provider->hasListeners(TestEvent::class));
    }

    public function testListenerProviderClearListeners(): void
    {
        $provider = new ListenerProvider();
        $provider->addListener(TestEvent::class, fn() => null);

        $this->assertTrue($provider->hasListeners(TestEvent::class));

        $provider->clearListeners(TestEvent::class);

        $this->assertFalse($provider->hasListeners(TestEvent::class));
    }

    public function testDispatcherReturnsSameEvent(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);

        $event = new TestEvent();
        $result = $dispatcher->dispatch($event);

        $this->assertSame($event, $result);
    }
}

class TestEvent
{
    public string $data = 'original';
}

class StoppableTestEvent extends StoppableEvent
{
}
