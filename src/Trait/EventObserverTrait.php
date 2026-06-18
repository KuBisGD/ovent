<?php

declare(strict_types=1);

namespace Ewn\Ovent\Trait;

use Ewn\Ovent\Listener;
use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventEmitterInterface;
use Closure;

/**
 * Trait for a default implementation of the {@see Ewn\Ovent\Interface\EventObserverInterface EventObserverInterface}
 */
trait EventObserverTrait
{
    /**
     * Array of listeners.
     *
     * @var array<string, array<int|string, Listener>>
     */
    private array $_listeners = [];

    /**
     * Adds a listener for an event.
     *
     * @param string $event Name of the event to listen on.
     * @param Closure(Event):void $callback A callback with the **Event** as the argument. Gets bound to the current object.
     * @param bool $once If the listener should only be called once.
     * @return Listener The resulting listener.
     */
    public function listenEvent(string $event, Closure $callback, bool $once = false): Listener
    {
        // $callback = $callback->bindTo($this);
        $listener = new Listener(
            belongsTo: $this, 
            name: $event, 
            callback: $callback, 
            once: $once
        );
        $this->_listeners[$event][] = $listener;
        return $listener;
    }

    /**
     * Add an existing listener to the observer.
     *
     * @param Listener $listener Listener to add.
     * @return void
     */
    public function addListener(Listener $listener): void
    {
        $this->_listeners[$listener->name][] = $listener;
    }

    /**
     * Removes a **Listener** from the observer.
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

    /**
     * Allows an observers to receive an event.
     *
     * Called by an **EventEmitter** when its {@see Ewn\Ovent\Interface\EventEmitterInterface::emitEvent() emitEvent}  method is called.
     *
     * @param Event $event
     * @return void
     */
    public function receiveEvent(Event $event): void
    {
        $listeners = $this->_listeners[$event->name] ?? null;

        if ($listeners) {
            foreach ($listeners as $id => $listener) {
                if ($event->active === false) {
                    break;
                }
                
                $listener($event);

                if ($listener->once) {
                    unset($this->_listeners[$event->name][$id]);
                    $this->_listeners[$event->name] = [...$this->_listeners[$event->name]];
                }
            }
        }

    }

    /**
     * Reverse alias for {@see Ewn\Ovent\Interface\EventEmitterInterface::attachObserver() EventEmitterInterface::attachObserver()}.
     *
     * @param EventEmitterInterface $emitter
     * @return void
     */
    public function observeEmitter(EventEmitterInterface ...$emitter): void
    {
        foreach ($emitter as $eventEmitter) {
            $eventEmitter->attachObserver($this);
        }
    }

    /**
     * Reverse alias for {@see Ewn\Ovent\Interface\EventEmitterInterface::detachObserver() EventEmitterInterface::detachObserver()}.
     *
     * @param EventEmitterInterface $emitter
     * @return void
     */
    public function forgetEmitter(EventEmitterInterface $emitter): void
    {
        $emitter->detachObserver($this);
    }
}
