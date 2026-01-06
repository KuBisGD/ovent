<?php

declare(strict_types=1);

namespace Kubis\Ovent\Interface;

/**
 * An interface that allows an object to observe and emit events.
 */
interface EventInterface extends EventEmitterInterface, EventObserverInterface
{
    // /**
    //  * Called by an **EventEmitter** when its dispatch method is called.
    //  *
    //  * @param Event $event
    //  * @return void
    //  */
    // public function receiveEvent(Event $event): void;

    // /**
    //  * Add an object to observe this emitter.
    //  *
    //  * @param EventObserverInterface $observer Object to attach.
    //  * @return void
    //  */
    // public function attachObserver(EventObserverInterface $observer): void;

    // /**
    //  * Remove an observer from this emitter.
    //  *
    //  * @param EventObserverInterface $observer Object to detach.
    //  * @return void
    //  */
    // public function detachObserver(EventObserverInterface $observer): void;

    // /**
    //  * Sends an event to all observers for this emitter.
    //  *
    //  * @param string $name Name of event.
    //  * @param null|array $data Data to send with the event.
    //  * @return void
    //  */
    // public function dispatchEvent(string $name, ?array $data = null): void;
}