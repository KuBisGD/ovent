<?php

declare(strict_types=1);

namespace Kubis\Ovent\Trait;

use Kubis\Ovent\Listener;
use Kubis\Ovent\Event;
use Kubis\Ovent\Interface\EventEmitterInterface;

trait EventObserverTrait
{
    /**
     * Array of listeners.
     *
     * @var array<string, array<int|string, Listener>>
     */
    private array $_listeners = [];

    // /**
    //  * Array of single use listeners.
    //  *
    //  * @var array<int|string, callable>
    //  */
    // private array $_onceListeners = [];

    /**
     * Adds a  listerner for an event.
     *
     * @param string $event Name of the event to listen on.
     * @param callable<Event> $callback A callback with the **Event** as the argument
     * @param null|string $id 
     * @param bool $once 
     * @return Listener
     */
    public function listenEvent(string $event, callable $callback, 
        // ?string $id = null,
        bool $once = false
    ): Listener {
        $listener = new Listener($event, $callback, $once);
        $this->_listeners[$event][] = $listener;
        return $listener;
    }

    public function removeListener(Listener $listener): void
    {
        foreach ($this->_listeners as $eventName => $listeners) {
            foreach ($listeners as $key => $storedListener) {
                if ($storedListener === $listener) {
                    unset($this->_listeners[$eventName][$key]);
                    $this->_listeners[$eventName] = [...$this->_listeners[$eventName]];
                }
            }
        }
    }

    public function receiveEvent(Event $event): void
    {
        $listeners = $this->_listeners[$event->name] ?? null;

        if ($listeners) {
            foreach ($listeners as $id => $listener) {
                $listener($event);
                if ($listener->once) {
                    unset($this->_listeners[$event->name][$id]);
                    $this->_listeners[$event->name] = [...$this->_listeners[$event->name]];
                }
            }
        }

    }

    /**
     * Reverse alias for EventEmitterInterface::attachObserver().
     *
     * @param EventEmitterInterface $emitter
     * @return void
     */
    public function observeEmitter(EventEmitterInterface $emitter): void
    {
        $emitter->attachObserver($this);
    }
}
