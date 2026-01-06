<?php

declare(strict_types=1);

namespace Kubis\Ovent\Trait;

use Kubis\Ovent\Interface\EventObserverInterface;
use Kubis\Ovent\Event;
use WeakReference;

trait EventEmitterTrait
{
    /**
     * Stores all observers.
     *
     * @var array<int, WeakReference<EventObserverInterface>>
     */
    private array $observers = [];

    public function attachObserver(EventObserverInterface $observer): void
    {
        $this->observers[] = WeakReference::create($observer);
    }

    public function detachObserver(EventObserverInterface $observer): void
    {
        foreach ($this->observers as $key => $weakRef) {
            $storedObserver = $weakRef->get();
            if ($storedObserver === $observer || $storedObserver === null) {
                unset($this->observers[$key]);
            }
        }
    }

    public function emitEvent(string $name, mixed $data = null): void
    {   
        $event = Event::create($this, $name, $data);
        foreach ($this->observers as $weakRef) {
            $observer = $weakRef->get();
            if ($observer) {
                $observer->receiveEvent($event);
            } else {
                unset($this->observers[$weakRef]);
            }
        }
    }
}
