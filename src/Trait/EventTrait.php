<?php

declare(strict_types=1);

namespace Kubis\Ovent\Trait;

trait EventTrait
{
    use EventEmitterTrait, EventObserverTrait;
}
