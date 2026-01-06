<?php

declare(strict_types=1);

namespace Ewn\Ovent\Trait;

use Ewn\Ovent\Listener;
use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventEmitterInterface;

trait EventObserverTrait
{
    /**
     * Array of listeners.
     *
     * @var array<string, array<int|string, Listener>>
     */
    private array $_listeners = [];

    /**
     * Adds a listerner for an event.
     *
     * @param string $event Name of the event to listen on.
     * @param callable<Event> $callback A callback with the **Event** as the argument
     * @param null|string $id 
     * @param bool $once 
     * @return Listener
     */
    public function listenEvent(string $event, callable $callback, bool $once = false): Listener
    {
        $listener = new Listener($event, $callback, $once);
        $this->_listeners[$event][] = $listener;
        return $listener;
    }

    /**
     * Removes a **Listener** from the observere.
     *
     * @param Listener $listener The **Listener** to remove.
     * @return void
     */
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

    /**
     * Reverse alias for EventEmitterInterface::detachObserver().
     *
     * @param EventEmitterInterface $emitter
     * @return void
     */
    public function forgetEmitter(EventEmitterInterface $emitter): void
    {
        $emitter->detachObserver($this);
    }
}
