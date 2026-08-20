<?php
declare(strict_types=1);

namespace Nexo\Events;

use Nexo\Contracts\Event;

final class EventDispatcher
{
    /** @var array<string, array{handler: EventHandler|callable, priority: int}[]> */
    private array $handlers = [];

    /** @var callable[] */
    private array $middleware = [];

    /** @var array<string, bool> */
    private array $subscribers = [];

    public function __construct()
    {
        $this->handlers = [];
        $this->middleware = [];
        $this->subscribers = [];
    }

    public function listen(string $eventName, EventHandler|callable $handler, int $priority = 0): void
    {
        if (!isset($this->handlers[$eventName])) {
            $this->handlers[$eventName] = [];
        }
        $this->handlers[$eventName][] = [
            'handler' => $handler,
            'priority' => $priority,
        ];
        // Sort by priority (higher first)
        usort($this->handlers[$eventName], fn ($a, $b) => $b['priority'] <=> $a['priority']);
    }

    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $class = $subscriber::class;
        if (isset($this->subscribers[$class])) {
            return;
        }

        $events = $subscriber::getSubscribedEvents();
        foreach ($events as $eventName => $config) {
            if (is_string($config)) {
                $this->listen($eventName, [$subscriber, $config]);
            } elseif (is_array($config)) {
                $method = $config['method'] ?? $eventName;
                $priority = $config['priority'] ?? 0;
                $this->listen($eventName, [$subscriber, $method], $priority);
            }
        }
        $this->subscribers[$class] = true;
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(Event $event): void
    {
        $metadata = EventMetadata::fromEvent($event);
        $handlers = $this->handlers[$event->eventName()] ?? [];

        if (empty($handlers)) {
            return;
        }

        // Build middleware chain
        $chain = function (Event $e, array $h) use ($handlers) {
            foreach ($h as $handlerData) {
                $handler = $handlerData['handler'];
                if ($handler instanceof EventHandler) {
                    if ($handler->supports($e->eventName())) {
                        $handler->handle($e);
                    }
                } else {
                    $handler($e);
                }
            }
        };

        // Apply middleware in reverse order (last added wraps first)
        for ($i = count($this->middleware) - 1; $i >= 0; $i--) {
            $middleware = $this->middleware[$i];
            $next = $chain;
            $chain = function (Event $e, array $h) use ($middleware, $next) {
                $middleware($e, $h, $next);
            };
        }

        $chain($event, $handlers);
    }

    public function dispatchSync(Event $event): void
    {
        $this->dispatch($event);
    }

    public function hasListeners(string $eventName): bool
    {
        return !empty($this->handlers[$eventName]);
    }

    public function getListeners(string $eventName): array
    {
        return $this->handlers[$eventName] ?? [];
    }

    public function removeListener(string $eventName, callable|EventHandler $handler): void
    {
        if (!isset($this->handlers[$eventName])) {
            return;
        }
        $this->handlers[$eventName] = array_filter(
            $this->handlers[$eventName],
            fn ($h) => $h['handler'] !== $handler
        );
    }
}