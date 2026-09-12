<?php

declare(strict_types=1);

use Ewn\Ovent\Attribute\BindTo;
use Ewn\Ovent\Enum\Scope;
use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventEmitterInterface;
use Ewn\Ovent\Interface\EventInterface;
use Ewn\Ovent\Interface\EventObserverInterface;
use Ewn\Ovent\Trait\EventEmitterTrait;
use Ewn\Ovent\Trait\EventObserverTrait;
use Ewn\Ovent\Trait\EventTrait;
use PHPUnit\Framework\TestCase;

final class CustomEvent extends Event
{
    public string $message;

    protected function __construct(
        EventEmitterInterface|EventInterface $target,
        string $name,
        mixed $detail,
    ) {
        parent::__construct($target, $name, $detail);
        $this->message = is_array($detail) ? ($detail['message'] ?? '') : (string) $detail;
    }
}

final class EventSystemTest extends TestCase
{
    public function testEmitterDispatchesEventToObserver(): void
    {
        $observer = new class implements EventObserverInterface {
            use EventObserverTrait;

            public array $received = [];

            public function receiveEvent(Event $event): void
            {
                $this->received[] = [
                    'name' => $event->name,
                    'detail' => $event->detail,
                    'target' => $event->target,
                ];
            }
        };

        $emitter = new class implements EventEmitterInterface {
            use EventEmitterTrait;
        };

        $observer->observeEmitter($emitter);

        $emitter->emitEvent('user.created', ['id' => 42]);

        $this->assertCount(1, $observer->received);
        $this->assertSame('user.created', $observer->received[0]['name']);
        $this->assertSame(['id' => 42], $observer->received[0]['detail']);
        $this->assertSame($emitter, $observer->received[0]['target']);
    }

    public function testListenerCanStopFurtherPropagation(): void
    {
        $emitter = new class implements EventEmitterInterface {
            use EventEmitterTrait;
        };

        $observer = new class($emitter) implements EventObserverInterface {
            use EventObserverTrait;

            public array $calls = [];

            public function __construct(private EventEmitterInterface $emitter)
            {
                $this->observeEmitter($this->emitter);
            }

            public function register(): void
            {
                $this->listenEvent('order.created', function (Event $event): void {
                    $this->calls[] = 'first';
                    $event->stopEvent();
                });

                $this->listenEvent('order.created', function (Event $event): void {
                    $this->calls[] = 'second';
                });
            }
        };

        $observer->register();
        $emitter->emitEvent('order.created');

        $this->assertSame(['first'], $observer->calls);
    }

    public function testListenerCanRunOnlyOnce(): void
    {
        $subject = new class implements EventInterface {
            use EventTrait;

            public int $count = 0;

            public function __construct()
            {
                $this->observeSelf();
                $this->listenEvent('ping', function (Event $event): void {
                    $this->count++;
                }, once: true);
            }
        };

        $subject->emitEvent('ping');
        $subject->emitEvent('ping');

        $this->assertSame(1, $subject->count);
    }

    public function testBindToAttributeCanAccessPublicAndPrivateMembers(): void
    {
        $subject = new class implements EventInterface {
            use EventTrait;

            public string $publicValue = 'public';

            private string $privateValue = 'private';

            public array $seen = [];

            public function __construct()
            {
                $this->observeSelf();
                $this->listenEvent('bound', #[BindTo(Scope::PUBLIC)] function (Event $event): void {
                    $this->seen[] = $this->publicValue;
                });
                $this->listenEvent('bound', #[BindTo(Scope::PRIVATE)] function (Event $event): void {
                    $this->seen[] = $this->privateValue;
                });
            }
        };

        $subject->emitEvent('bound');

        $this->assertSame(['public', 'private'], $subject->seen);
    }

    public function testListenerLifecycleMethodsAndReplacementWork(): void
    {
        $subject = new class implements EventInterface {
            use EventTrait;

            public string $value = 'initial';

            public function __construct()
            {
                $this->observeSelf();
            }
        };

        $listener = $subject->listenEvent('lifecycle', function (Event $event) use ($subject): void {
            $subject->value = 'before';
        });

        $subject->emitEvent('lifecycle');
        $this->assertSame('before', $subject->value);
        $this->assertSame(1, $listener->calls);
        $this->assertSame(1, $listener->invokes);

        $listener->deactivate();
        $subject->emitEvent('lifecycle');
        $this->assertSame('before', $subject->value);
        $this->assertSame(2, $listener->calls);
        $this->assertSame(1, $listener->invokes);

        $listener->activate();
        $listener->replaceCallback(function (Event $event) use ($subject): void {
            $subject->value = 'after';
        });

        $subject->emitEvent('lifecycle');
        $this->assertSame('after', $subject->value);
        $this->assertSame(3, $listener->calls);
        $this->assertSame(2, $listener->invokes);

        $listener->resetCounters();
        $this->assertSame(0, $listener->calls);
        $this->assertSame(0, $listener->invokes);
    }

    public function testObserverHelpersAndRemovalWork(): void
    {
        $subject = new class implements EventInterface {
            use EventTrait;

            public array $seen = [];

            public function __construct()
            {
                $this->observeSelf();
            }
        };

        $first = $subject->listenEvent('batch', function (Event $event) use ($subject): void {
            $subject->seen[] = 'first';
        });

        $second = $subject->listenEvent('batch', function (Event $event) use ($subject): void {
            $subject->seen[] = 'second';
        });

        $this->assertTrue($subject->isListeningFor('batch'));
        $this->assertSame(['batch'], $subject->getObservedEvents());
        $this->assertCount(2, $subject->getListenersFor('batch'));

        $subject->removeListener($first);
        $subject->emitEvent('batch');

        $this->assertSame(['second'], $subject->seen);
        $this->assertCount(1, $subject->getListenersFor('batch'));

        $subject->addListener($first);
        $this->assertCount(2, $subject->getListenersFor('batch'));

        $subject->removeListener($second);
        $this->assertCount(1, $subject->getListenersFor('batch'));
    }

    public function testMutualObservationAndEmitterBindingsWork(): void
    {
        $left = new class implements EventInterface {
            use EventTrait;

            public int $count = 0;

            public function __construct()
            {
                $this->observeSelf();
            }
        };

        $right = new class implements EventInterface {
            use EventTrait;

            public int $count = 0;

            public function __construct()
            {
                $this->observeSelf();
            }
        };

        $left->observeMutual($right);

        $left->listenEvent('ping', function (Event $event) use ($left): void {
            $left->count++;
        });

        $right->emitEvent('ping');

        $this->assertSame(1, $left->count);

        $emitter = new class implements EventEmitterInterface {
            use EventEmitterTrait;
        };

        $observer = new class implements EventObserverInterface {
            use EventObserverTrait;

            public bool $received = false;

            public function receiveEvent(Event $event): void
            {
                $this->received = true;
            }
        };

        $observer->observeEmitter($emitter);
        $emitter->emitEvent('ready');
        $this->assertTrue($observer->received);

        $observer->forgetEmitter($emitter);
        $observer->received = false;
        $emitter->emitEvent('ready');
        $this->assertFalse($observer->received);
    }

    public function testEmitterCanDispatchCustomEventType(): void
    {
        $emitter = new class implements EventEmitterInterface {
            use EventEmitterTrait;
        };

        $observer = new class implements EventObserverInterface {
            use EventObserverTrait;

            public ?CustomEvent $event = null;

            public function receiveEvent(Event $event): void
            {
                if ($event instanceof CustomEvent) {
                    $this->event = $event;
                }
            }
        };

        $emitter->attachObserver($observer);
        $emitter->emitEvent('custom.created', ['message' => 'hello'], CustomEvent::class);

        $this->assertInstanceOf(CustomEvent::class, $observer->event);
        $this->assertSame('custom.created', $observer->event->name);
        $this->assertSame('hello', $observer->event->message);
    }

    public function testEmitterObserverLifecycleAndQueryMethodsWork(): void
    {
        $emitter = new class implements EventEmitterInterface {
            use EventEmitterTrait;
        };

        $first = new class implements EventObserverInterface {
            use EventObserverTrait;

            public function receiveEvent(Event $event): void
            {
            }
        };

        $second = new class implements EventObserverInterface {
            use EventObserverTrait;

            public function receiveEvent(Event $event): void
            {
            }
        };

        $third = new class implements EventObserverInterface {
            use EventObserverTrait;

            public function receiveEvent(Event $event): void
            {
            }
        };

        $emitter->attachObserver([$first, $second]);

        $this->assertTrue($emitter->isObservedBy($first));
        $this->assertFalse($emitter->isObservedBy($third));
        $this->assertSame([$first, $second], $emitter->getObserverOrdering());

        $emitter->detachObserver($first);

        $this->assertFalse($emitter->isObservedBy($first));
        $this->assertSame([$second], $emitter->getObserverOrdering());

        $emitter->attachObserver($third);
        $this->assertSame([$second, $third], $emitter->getObserverOrdering());

        $emitter->detachAllObservers();

        $this->assertSame([], $emitter->getObserverOrdering());
        $this->assertFalse($emitter->isObservedBy($second));
        $this->assertFalse($emitter->isObservedBy($third));
    }

    public function testEmitterRejectsInvalidObserverArrays(): void
    {
        $emitter = new class implements EventEmitterInterface {
            use EventEmitterTrait;
        };

        $valid = new class implements EventObserverInterface {
            use EventObserverTrait;

            public function receiveEvent(Event $event): void {}
        };

        $emitter->attachObserver([$valid]);

        $this->expectException(\InvalidArgumentException::class);
        $emitter->attachObserver([$valid, 'not-an-observer']);
    }
}
