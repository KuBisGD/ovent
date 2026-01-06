<?php

declare(strict_types=1);

namespace Kubis\Ovent;

use Kubis\Ovent\Interface\EventEmitterInterface;
use Kubis\Ovent\Interface\EventInterface;

/**
 * Object representing an **Event**
 */
class Event
{
    /**
     * Constructor
     *
     * @param EventEmitterInterface|EventInterface $target Object that dispatched the event.
     * @param string $name Name of the **Event**.
     * @param mixed $detail Custom **Event** data.
     */
    private function __construct(
        public private(set) EventEmitterInterface|EventInterface $target,
        public private(set) string $name,
        public private(set) mixed $detail,
    ) {}
    
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
