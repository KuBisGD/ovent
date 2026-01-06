<?php

declare(strict_types=1);

namespace Ewn\Ovent\Trait;

use Ewn\Ovent\Interface\EventObserverInterface;
use Ewn\Ovent\Event;
use WeakReference;

trait EventEmitterTrait
{
    /**
     * Stores all observers.
     *
     * @var array<int, WeakReference<EventObserverInterface>>
     */
    private array $_observers = [];

    public function attachObserver(EventObserverInterface $observer): void
    {
        $this->_observers[] = WeakReference::create($observer);
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

    public function emitEvent(string $name, mixed $data = null): void
    {   
        $event = Event::create($this, $name, $data);
        foreach ($this->_observers as $weakRef) {
            $observer = $weakRef->get();
            if ($observer) {
                $observer->receiveEvent($event);
            } else {
                unset($this->_observers[$weakRef]);
            }
        }
    }
}
