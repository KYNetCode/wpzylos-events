<?php

defined('ABSPATH') || exit;

declare(strict_types=1);

namespace WPZylos\Framework\Events\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Events\StoppableEvent;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Tests for StoppableEvent abstract base class.
 */
class StoppableEventTest extends TestCase
{
    public function testImplementsPsrInterface(): void
    {
        $event = new ConcreteStoppableEvent();
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
    }

    public function testPropagationNotStoppedByDefault(): void
    {
        $event = new ConcreteStoppableEvent();
        $this->assertFalse($event->isPropagationStopped());
    }

    public function testStopPropagationStopsEvent(): void
    {
        $event = new ConcreteStoppableEvent();
        $event->stopPropagation();

        $this->assertTrue($event->isPropagationStopped());
    }
}

class ConcreteStoppableEvent extends StoppableEvent
{
}
