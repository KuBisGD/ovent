<?php

declare(strict_types=1);

namespace Ewn\Ovent\Trait;

use Ewn\Ovent\Interface\EventInterface;

/**
 * Adds the ability to observe and emit events.
 */
trait EventTrait
{
    use EventEmitterTrait, EventObserverTrait;

    /**
     * Makes two objects observe each other.
     *
     * @param EventInterface $emitterObserver
     * @return void
     */
    public function observeMutual(EventInterface $emitterObserver): void
    {
        $this->attachObserver($emitterObserver);
        $emitterObserver->attachObserver($this);
    }

    /**
     * Makes the object observe itself.
     *
     * @return void
     */
    protected function observeSelf(): void
    {
        $this->attachObserver($this);
    }
}
