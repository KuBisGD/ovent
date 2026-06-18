<?php

declare(strict_types=1);

namespace Ewn\Ovent\Enum;

/**
 * The scope to bind a listener to.
 */
enum Scope {
    /**
     * Binds to the public scope.
     */
    case PUBLIC;

    /**
     * Binds to the private scope.
     */
    case PRIVATE;
}
