<?php
declare(strict_types=1);

namespace Nexo\Events;

use Nexo\Contracts\Event;
use Nexo\Database\Connection;
use Nexo\Database\PdoRepository;
use Nexo\Contracts\TransactionManager;
use Nexo\Domain\Result;

final class PdoEventStore extends PdoRepository implements EventStore
{
    protected function tableName(): string
    {
        return 'nexo_events';
    }

    protected function entityClass(): string
    {
        return Event::class;
    }

    protected function mapRow(array $row): object
    {
        // This is a generic implementation - specific event types would need custom hydration
        return new class($row) implements Event {
            private array $data;
            public function __construct(array $data) { $this->data = $data; }
            public function eventId(): string { return $this->data['event_id']; }
            public function eventName(): string { return $this->data['event_name']; }
            public function eventVersion(): int { return (int) $this->data['event_version']; }
            public function occurredAt(): \DateTimeImmutable { return new \DateTimeImmutable($this->data['occurred_at']); }
            public function actorId(): ?string { return $this->data['actor_id'] ?? null; }
            public function tenantId(): ?string { return $this->data['tenant_id'] ?? null; }
            public function correlationId(): ?string { return $this->data['correlation_id'] ?? null; }
            public function causationId(): ?string { return $this->data['causation_id'] ?? null; }
            public function payload(): array { return $this->data['payload'] ?? []; }
        };
    }

    protected function mapEntity(object $entity): array
    {
        throw new \LogicException('Use append() instead of save() for events');
    }

    public function append(Event $event): Result
    {
        return $this->transactionManager->run(function () use ($event) {
            $metadata = EventMetadata::fromEvent($event);
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->tableName()} 
                (event_id, event_name, event_version, occurred_at, actor_id, tenant_id, correlation_id, causation_id, payload)
                VALUES (:event_id, :event_name, :event_version, :occurred_at, :actor_id, :tenant_id, :correlation_id, :causation_id, :payload)"
            );
            $stmt->execute([
                ':event_id' => $metadata->eventId(),
                ':event_name' => $metadata->eventName(),
                ':event_version' => $metadata->eventVersion(),
                ':occurred_at' => $metadata->occurredAt()->format(\DateTimeInterface::ATOM),
                ':actor_id' => $metadata->actorId(),
                ':tenant_id' => $metadata->tenantId(),
                ':correlation_id' => $metadata->correlationId(),
                ':causation_id' => $metadata->causationId(),
                ':payload' => json_encode($event->payload()),
            ]);
            return Result::ok($event);
        });
    }

    public function get(string $eventId): Result
    {
        return $this->transactionManager->run(function () use ($eventId) {
            $stmt = $this->connection->prepare(
                "SELECT * FROM {$this->tableName()} WHERE event_id = :id"
            );
            $stmt->execute([':id' => $eventId]);
            $row = $stmt->fetch();
            return $row ? Result::ok($this->mapRow($row)) : Result::err(new \RuntimeException("Event {$eventId} not found"));
        });
    }

    /** @return Event[] */
    public function getByCorrelationId(string $correlationId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tableName()} WHERE correlation_id = :cid ORDER BY occurred_at ASC"
        );
        $stmt->execute([':cid' => $correlationId]);
        return array_map(fn ($row) => $this->mapRow($row), $stmt->fetchAll());
    }

    /** @return Event[] */
    public function getByActorId(string $actorId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tableName()} WHERE actor_id = :aid ORDER BY occurred_at DESC"
        );
        $stmt->execute([':aid' => $actorId]);
        return array_map(fn ($row) => $this->mapRow($row), $stmt->fetchAll());
    }

    /** @return Event[] */
    public function getByTenantId(string $tenantId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tableName()} WHERE tenant_id = :tid ORDER BY occurred_at DESC"
        );
        $stmt->execute([':tid' => $tenantId]);
        return array_map(fn ($row) => $this->mapRow($row), $stmt->fetchAll());
    }

    /** @return Event[] */
    public function getByDateRange(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tableName()} WHERE occurred_at BETWEEN :from AND :to ORDER BY occurred_at ASC"
        );
        $stmt->execute([
            ':from' => $from->format(\DateTimeInterface::ATOM),
            ':to' => $to->format(\DateTimeInterface::ATOM),
        ]);
        return array_map(fn ($row) => $this->mapRow($row), $stmt->fetchAll());
    }
}