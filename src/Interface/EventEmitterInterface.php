<?php

declare(strict_types=1);

namespace Kubis\Ovent\Interface;

/**
 * An interface that allow an object to be observed and emit events.
 */
interface EventEmitterInterface
{
    /**
     * Add an object to observe this emitter.
     *
     * @param EventObserverInterface $observer Object to attach.
     * @return void
     */
    public function attachObserver(EventObserverInterface $observer): void;

    /**
     * Remove an observer from this emitter.
     *
     * @param EventObserverInterface $observer Object to detach.
     * @return void
     */
    public function detachObserver(EventObserverInterface $observer): void;

    /**
     * Sends an event to all observers for this emitter.
     *
     * @param string $name Name of event.
     * @param mixed $data Data to send with the event.
     * @return void
     */
    public function emitEvent(string $name, mixed $data = null): void;
}
