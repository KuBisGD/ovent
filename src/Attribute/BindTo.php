<?php

declare(strict_types=1);

namespace Ewn\Ovent\Attribute;

use Attribute;
use Ewn\Ovent\Enum\Scope;

/**
 * Use on a listeners Closure to bind its scope to the main object.
 */
#[Attribute(Attribute::TARGET_FUNCTION)]
final readonly class BindTo
{
    public function __construct(public Scope $scope) {}
}
