<?php
declare(strict_types=1);

namespace Nexo\Events;

final readonly class EventMetadata
{
    private string $eventId;
    private string $eventName;
    private int $eventVersion;
    private \DateTimeImmutable $occurredAt;
    private ?string $actorId;
    private ?string $tenantId;
    private ?string $correlationId;
    private ?string $causationId;

    public function __construct(
        string $eventId,
        string $eventName,
        int $eventVersion,
        \DateTimeImmutable $occurredAt,
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $correlationId = null,
        ?string $causationId = null
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->eventVersion = $eventVersion;
        $this->occurredAt = $occurredAt;
        $this->actorId = $actorId;
        $this->tenantId = $tenantId;
        $this->correlationId = $correlationId;
        $this->causationId = $causationId;
    }

    public static function fromEvent(\Nexo\Contracts\Event $event): self
    {
        return new self(
            $event->eventId(),
            $event->eventName(),
            $event->eventVersion(),
            $event->occurredAt(),
            $event->actorId(),
            $event->tenantId(),
            $event->correlationId(),
            $event->causationId() ?? null
        );
    }

    public function eventId(): string { return $this->eventId; }
    public function eventName(): string { return $this->eventName; }
    public function eventVersion(): int { return $this->eventVersion; }
    public function occurredAt(): \DateTimeImmutable { return $this->occurredAt; }
    public function actorId(): ?string { return $this->actorId; }
    public function tenantId(): ?string { return $this->tenantId; }
    public function correlationId(): ?string { return $this->correlationId; }
    public function causationId(): ?string { return $this->causationId; }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->eventName,
            'event_version' => $this->eventVersion,
            'occurred_at' => $this->occurredAt->format(\DateTimeInterface::ATOM),
            'actor_id' => $this->actorId,
            'tenant_id' => $this->tenantId,
            'correlation_id' => $this->correlationId,
            'causation_id' => $this->causationId,
        ];
    }
}