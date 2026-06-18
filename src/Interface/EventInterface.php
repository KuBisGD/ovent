<?php

declare(strict_types=1);

namespace Ewn\Ovent\Interface;

/**
 * An interface that allows an object to observe and emit events.
 */
interface EventInterface extends EventEmitterInterface, EventObserverInterface
{
    /**
     * Makes two objects observe each other.
     *
     * @param EventInterface $observer
     * @return void
     */
    public function observeMutual(EventInterface $observer): void;
}
