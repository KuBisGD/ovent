<?php

declare(strict_types=1);

namespace Ewn\Ovent;

use Ewn\Ovent\Interface\EventEmitterInterface;
use Ewn\Ovent\Interface\EventInterface;

/**
 * Object representing an **Event**
 */
class Event
{
    /**
     * The time in microseconds at which the event was created.
     */
    readonly public float $timeStamp;

    /**
     * If the event should continue to the rest of the listeners
     */
    public private(set) bool $active = true;

    /**
     * Constructor
     *
     * @param EventEmitterInterface|EventInterface $target Object that dispatched the event.
     * @param string $name Name of the **Event**.
     * @param mixed $detail Custom **Event** data.
     */
    private function __construct(
        readonly public EventEmitterInterface|EventInterface $target,
        readonly public string $name,
        readonly public mixed $detail,
    ) {
        $this->timeStamp = microtime(as_float: true);
    }
    
    /**
     * Create a new **Event**.
     *
     * @param EventEmitterInterface|EventInterface $target Object that dispatched the event.
     * @param string $name Name of the **Event**.
     * @param mixed $detail Custom data to add to the **Event**.
     * @return Event
     */
    public static function create(EventEmitterInterface $target, string $name, mixed $detail): self
    {
        return new static($target, $name, $detail);
    }

    /**
     * Stops the event from continuing to other listeners
     *
     * @return void
     */
    public function stopEvent(): void
    {
        $this->active = false;
    }
}
