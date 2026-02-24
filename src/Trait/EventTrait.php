<?php

declare(strict_types=1);

namespace Ewn\Ovent\Trait;

use Ewn\Ovent\Interface\EventInterface;

trait EventTrait
{
    use EventEmitterTrait, EventObserverTrait;

    /**
     * Makes two objects observe each other.
     *
     * @param EventInterface $observer
     * @return void
     */
    public function observeMutual(EventInterface $emitterObserver): void
    {
        $this->attachObserver($emitterObserver);
        $emitterObserver->attachObserver($this);
    }
}
