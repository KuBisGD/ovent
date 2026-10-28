<?php

declare(strict_types=1);

namespace Ewn\Ovent\Interface;

use BackedEnum;
use Ewn\Ovent\Event;

/**
 * An interface that allow an object to be observed and emit events.
 */
interface EventEmitterInterface
{
    /**
     * Add an object to observe this emitter.
     *
     * @param EventObserverInterface|EventObserverInterface[] $observer Object to attach.
     * @return void
     */
    public function attachObserver(EventObserverInterface|array $observer): void;
    // public function attachObserver(EventObserverInterface ...$observer): void;

    /**
     * Remove an observer from this emitter.
     *
     * @param EventObserverInterface $observer Object to detach.
     * @return void
     */
    public function detachObserver(EventObserverInterface $observer): void;

    /**
     * Removes all Observers from this emitter.
     *
     * @return void
     */
    public function detachAllObservers(): void;

    /**
     * Sends an event to all observers for this emitter.
     *
     * @param string|BackedEnum<string> $name Name of event or a backed enum.
     * @param mixed $data Data to send with the event.
     * @param class-string<Event> $eventType Set to use custom event type.
     * @return void
     */
    public function emitEvent(string|BackedEnum $name, mixed $data = null, string $eventType = Event::class): void;
}
