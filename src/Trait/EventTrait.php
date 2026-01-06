<?php

declare(strict_types=1);

namespace Ewn\Ovent\Trait;

trait EventTrait
{
    use EventEmitterTrait, EventObserverTrait;
}
