<?php

declare(strict_types=1);

namespace Events\Trait;

trait EventTrait
{
    use EventEmitterTrait, EventObserverTrait;
}
