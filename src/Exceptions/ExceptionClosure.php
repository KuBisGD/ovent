<?php

declare(strict_types=1);

namespace Ewn\Ovent\Exceptions;

use Closure;
use Exception;
use Throwable;

/**
 * An exception that takes the form of a closure.
 */
class ExceptionClosure extends Exception
{
    private function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __invoke(): void
    {
        throw $this;
    }
    
    /**
     * Creates a Closure that throws an exception if called.
     *
     * @param string $message
     * @param integer $code
     * @param Throwable|null $previous
     * @return Closure
     */
    public static function new(string $message = '', int $code = 0, ?Throwable $previous = null): Closure
    {
        $new = new static($message, $code, $previous);

        return Closure::fromCallable($new);
    }
}