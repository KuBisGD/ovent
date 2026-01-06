<?php

declare(strict_types=1);

namespace Kubis\Ovent;

use Kubis\Ovent\Event;

class Listener
{
    private $callback;

    /**
     * The amount of times this **Listener** has been called.
     */
    public private(set) int $calls = 0;

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

    public function __invoke(Event $event)
    {
        $this->calls++;
        call_user_func($this->callback, $event);
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
