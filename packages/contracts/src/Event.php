<?php
declare(strict_types=1);

namespace Nexo\Contracts;

interface Event
{
    public function eventId(): string;

    public function eventName(): string;

    public function eventVersion(): int;

    public function occurredAt(): \DateTimeImmutable;

    public function actorId(): ?string;

    public function tenantId(): ?string;

    public function correlationId(): ?string;

    public function payload(): array;
}