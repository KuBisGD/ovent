<?php

declare(strict_types=1);

namespace Ewn\Ovent;

use Ewn\Ovent\Event;

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
    public bool $active = true;

    /**
     * @var callable
     */
    private $callback;

    /**
     * Constructor
     *
     * @param string $name
     * @param callable $callback
     * @param boolean $once
     */
    public function __construct(
        public private(set) string $name,
        callable $callback,
        public private(set) bool $once
    ) {
        $this->callback = $callback;
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
     * Replace this **Listener**s callback with a new callable.
     *
     * @param callable $newCallback The new callback.
     * @return void
     */
    public function replaceCallback(callable $newCallback): void
    {
        $this->callback = $newCallback;
    }
}
