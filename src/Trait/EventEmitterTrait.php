<?php

declare(strict_types=1);

namespace Ewn\Ovent\Trait;

use Ewn\Ovent\Interface\EventObserverInterface;
use Ewn\Ovent\Event;
use InvalidArgumentException;
use WeakReference;

/**
 * Trait for a default implementation of the {@see Ewn\Ovent\Interface\EventEmitterInterface EventEmitterInterface}
 */
trait EventEmitterTrait
{
    /**
     * Stores all observers.
     *
     * @var array<int, WeakReference<EventObserverInterface>>
     */
    private array $_observers = [];

    /**
     * Add an object to observe this emitter.
     * 
     * (The attached observer will be weakly stored in the emitter)
     * 
     * @param EventObserverInterface|array $observer
     * @return void
     * 
     * @throws InvalidArgumentException Argument is not an observer.
     */
    public function attachObserver(EventObserverInterface|array $observer): void
    {
        if ($observer instanceof EventObserverInterface) {
           $this->_observers[] = WeakReference::create($observer);
           return;
        }

        foreach ($observer as $eventObserver) {
            if ($eventObserver instanceof EventObserverInterface) {
                $this->_observers[] = WeakReference::create($eventObserver);
            } else {
                throw new InvalidArgumentException('array must only contain observers');
            }
            
        }
    }

    public function detachObserver(EventObserverInterface $observer): void
    {
        foreach ($this->_observers as $key => $weakRef) {
            $storedObserver = $weakRef->get();
            if ($storedObserver === $observer || $storedObserver === null) {
                unset($this->_observers[$key]);
            }
        }
    }

    /**
     * Removes all Observers from this emitter.
     *
     * @return void
     */
    public function detachAllObservers(): void
    {
        $this->_observers = [];
    }

    public function emitEvent(string $name, mixed $data = null, string $eventType = Event::class): void
    {   
        $event = $eventType::create($this, $name, $data);
        foreach ($this->_observers as $key => $weakRef) {
            $observer = $weakRef->get();
            if ($observer) {
                $observer->receiveEvent($event);
            } else {
                unset($this->_observers[$key]);
            }

            
            // stop event if not active (mby don't use)
            if ($event->active === false) {
                break;
            }
        }
    }
}
