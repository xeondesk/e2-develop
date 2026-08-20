<?php
declare(strict_types=1);

namespace Nexo\Events;

use Nexo\Contracts\Event;
use Nexo\Domain\Result;

interface EventStore
{
    public function append(Event $event): Result;

    public function get(string $eventId): Result;

    /** @return Event[] */
    public function getByCorrelationId(string $correlationId): array;

    /** @return Event[] */
    public function getByActorId(string $actorId): array;

    /** @return Event[] */
    public function getByTenantId(string $tenantId): array;

    /** @return Event[] */
    public function getByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array;
}