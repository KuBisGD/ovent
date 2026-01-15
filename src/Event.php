<?php

declare(strict_types=1);

namespace Ewn\Ovent;

use Ewn\Ovent\Interface\EventEmitterInterface;
use Ewn\Ovent\Interface\EventInterface;

/**
 * Object representing an **Event**
 */
readonly class Event
{
    /**
     * The time in microseconds at which the event was created.
     */
    public float $timeStamp;

    /**
     * Constructor
     *
     * @param EventEmitterInterface|EventInterface $target Object that dispatched the event.
     * @param string $name Name of the **Event**.
     * @param mixed $detail Custom **Event** data.
     */
    private function __construct(
        public EventEmitterInterface|EventInterface $target,
        public string $name,
        public mixed $detail,
    ) {
        $this->timeStamp = microtime(as_float: true);
    }
    
    /**
     * Create a new **Event**.
     *
     * @param EventEmitterInterface|EventInterface $target Object that dispatched the event.
     * @param string $name Name of the **Event**.
     * @param mixed $detail Custom data to add to the **Event**.
     * @return self
     */
    public static function create(EventEmitterInterface $target, string $name, mixed $detail): self
    {
        return new self($target, $name, $detail);
    }
}
