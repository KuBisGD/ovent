<?php

declare(strict_types=1);

namespace Ewn\Ovent;

use Closure;
use Ewn\Ovent\Attribute\BindTo;
use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventObserverInterface;
use Exception;
use ReflectionFunction;
use Ewn\Ovent\Enum\Scope;
use Ewn\Ovent\Exceptions\ExceptionClosure;

/**
 * Represents a listener for an event.
 */
class Listener
{
    /**
     * The amount of times this **Listener**s callable has been called.
     */
    public private(set) int $calls = 0;

    /**
     * The amount of times the **Listener** has been invoked.
     */
    public private(set) int $invokes = 0;

    /**
     * If the Listener should be called or not.
     *
     * @var boolean
     */
    public private(set) bool $active = true;

    /**
     * The current scope that the listener is bound to.
     */
    public private(set) ?Scope $scope;

    /**
     * Type of the listeners owner.
     *
     * @var string
     */
    public string $ownerType {
        get {
            return $this->belongsTo::class;
        }
    }

    /**
     * @var Closure(Event):void
     */
    private Closure $callback;

    /**
     * Constructor
     *
     * @param string $name
     * @param Closure(Event):void $callback
     * @param boolean $once
     */
    public function __construct(
        private EventObserverInterface $belongsTo,
        public private(set) string $name,
        Closure $callback,
        public private(set) bool $once
    ) {
        $this->replaceCallback($callback);
    }

    public function __invoke(Event $event): void
    {
        $this->calls++;
        if ($this->active) {
            $this->invokes++;
            call_user_func($this->callback, $event);
        }
    }

    /**
     * Replace this **Listener**s callback with a new Closure.
     *
     * @param Closure(Event):void $newCallback The new callback.
     * @return void
     * 
     * @throws Exception If the Closure could not be bound to the observer.
     */
    public function replaceCallback(Closure $newCallback): void
    {
        $this->scope = null;
        $closure = $this->handleAttributes($newCallback);
        $this->callback = $closure 
            ?? ExceptionClosure::new('closure was not set correctly');
            // ?? fn (Event $e) => throw new Exception('listener was not bound to an observer for '.$e->name.' on '.static::class);

        if ($closure === null) {
            throw new Exception('could not bind listener to observer');
        }
    }

    /**
     * Makes the listener active.
     *
     * @return void
     */
    public function activate(): void
    {
        $this->active = true;
    }

    /**
     * Deactivates the listener.
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->active = false;
    }

    /**
     * Sets the call amount back to 0.
     *
     * @return void
     */
    public function resetCallCounter(): void
    {
        $this->calls = 0;
    }

    /**
     * Sets the invoke amount back to 0.
     *
     * @return void
     */
    public function resetInvokeCounter(): void
    {
        $this->invokes = 0;
    }

    /**
     * Resets all counters.
     *
     * @return void
     */
    public function resetCounters(): void
    {
        $this->resetCallCounter();
        $this->resetInvokeCounter();
    }

    /**
     * Handles potential Attributes on the closure.
     *
     * @param Closure $closure
     * @return null|Closure
     */
    private function handleAttributes(Closure $closure): ?Closure
    {
        $ref = new ReflectionFunction($closure);
        $attributes = $ref->getAttributes();

        foreach ($attributes as $attribute) {
            switch ($attribute->name) {
                case BindTo::class:
                    $att = $attribute->newInstance();
                    $newBound = $closure->bindTo(
                        newThis: $this->belongsTo,
                        newScope: ($att->scope === Scope::PRIVATE) ? $this->belongsTo : null
                    );
                    
                    if ($newBound === null) {
                        return null;
                    }

                    $this->scope = $att->scope;

                    $closure = $newBound;
                    break;
                default:
                    break;
            }
        }

        return $closure;
    }
}
