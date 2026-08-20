<?php
declare(strict_types=1);

namespace Nexo\Domain;

use Nexo\Contracts\DomainEvent;

/** @extends Entity<string> */
abstract class AbstractAggregateRoot implements AggregateRoot
{
    /** @var array<int, DomainEvent> */
    private array $domainEvents = [];

    abstract public function id(): string;

    public function equals(Entity $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }
        return $this->id() === $other->id();
    }

    /** @return array<int, DomainEvent> */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    public function recordThat(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
}