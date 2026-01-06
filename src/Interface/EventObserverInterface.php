<?php

declare(strict_types=1);

namespace Kubis\Ovent\Interface;

use Kubis\Ovent\Event;

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
