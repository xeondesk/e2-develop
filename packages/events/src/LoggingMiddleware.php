<?php
declare(strict_types=1);

namespace Nexo\Events;

use Nexo\Contracts\Event;
use Psr\Log\LoggerInterface;

final class LoggingMiddleware
{
    public function __construct(private LoggerInterface $logger) {}

    public function __invoke(Event $event, array $handlers, callable $next): void
    {
        $this->logger->info('Event dispatched', [
            'event' => $event->eventName(),
            'event_id' => $event->eventId(),
            'handlers' => count($handlers),
        ]);

        $start = microtime(true);
        try {
            $next($event, $handlers);
            $duration = microtime(true) - $start;
            $this->logger->info('Event processed', [
                'event' => $event->eventName(),
                'duration_ms' => round($duration * 1000, 2),
            ]);
        } catch (\Throwable $e) {
            $duration = microtime(true) - $start;
            $this->logger->error('Event failed', [
                'event' => $event->eventName(),
                'error' => $e->getMessage(),
                'duration_ms' => round($duration * 1000, 2),
            ]);
            throw $e;
        }
    }
}