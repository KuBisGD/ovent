<?php

declare(strict_types=1);

namespace Ewn\Ovent\Interface;

use Ewn\Ovent\Event;

/**
 * An interface that allow an object to observe an event emitter.
 */
interface EventObserverInterface
{
    /**
     * Called by an **EventEmitter** when its dispatch method is called.
     *
     * @param Event $event
     * @return void
     */
    public function receiveEvent(Event $event): void;
}
