# Ewn\Ovent

---

(wip)

## Usage

---

### Basic observation

Here `A` can only observe and `B` can only emit events

```php
<?php
use Ewn\Ovent\Interface\EventEmitterInterface;
use Ewn\Ovent\Interface\EventObserverInterface;
use Ewn\Ovent\Trait\EventEmitterTrait;
use Ewn\Ovent\Trait\EventObserverTrait;
use Ewn\Ovent\Event;

class A implements EventObserverInterface
{
    use EventObserverTrait;

    public function __construct()
    {
        $this->listenEvent('event-name', function (Event $event) {
            var_dump($event);
        });
    }
}

class B implements EventEmitterInterface
{
    use EventEmitterTrait;

    public function send(): void
    {
        $this->emitEvent('event-name');
    }
}

$observer = new A;
$emitter = new B;

$observer->observeEmitter($emitter);
// or $emitter->attachObserver($observer);

$emitter->send();
```

### Observing and Emitting from the same object

```php
<?php
use Ewn\Ovent\Attribute\BindTo;
use Ewn\Ovent\Enum\Scope;
use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventInterface;
use Ewn\Ovent\Trait\EventTrait;

class A implements EventInterface
{
    use EventTrait;

    public string $public = 'is public';

    private string $private = 'is private';

    public function __construct()
    {
        $this->observeSelf();
    }
}

$a = new A;

// Does not have access to $this
$a->listenEvent('event-1', function (Event $event) {
    echo 'From ', $event->name, PHP_EOL;
});

// Does have access to $this, but only public properties.
$a->listenEvent('event-2', #[BindTo(Scope::PUBLIC)] function (Event $event) {
    echo 'From ', $event->name, ' ', $this->public, PHP_EOL;
});

// Has full access to $this.
$a->listenEvent('event-3', #[BindTo(Scope::PRIVATE)] function (Event $event) {
    echo 'From ', $event->name, ' ', $this->private, PHP_EOL;
});

$a->emitEvent('event-1');
$a->emitEvent('event-2');
$a->emitEvent('event-3');
```

_Output:_

```
From event-1
From event-2 is public
From event-3 is private
```

### Updating and removing Listeners

```php
<?php
use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventInterface;
use Ewn\Ovent\Trait\EventTrait;

class A implements EventInterface
{
    use EventTrait;

    public function __construct()
    {
        $this->observeSelf();
    }
}

$a = new A;

/** @var \Ewn\Ovent\Listener $listener  */
$listener = $a->listenEvent('event-1', function (Event $event) {
    echo 'From ', $event->name, PHP_EOL;
});

$a->emitEvent('event-1');
// > "From event-1"

$listener->replaceCallback(function (Event $event) {
    echo 'Still form ', $event->name, PHP_EOL; 
});

$a->emitEvent('event-1');
// > "Still form event-1"

$a->removeListener($listener);

```