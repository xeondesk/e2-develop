<?php
declare(strict_types=1);

namespace Nexo\Domain;

use Nexo\Contracts\DomainEvent as ContractsDomainEvent;

/**
 * @extends Entity<string>
 */
interface AggregateRoot extends Entity
{
    /** @return array<int, ContractsDomainEvent> */
    public function pullDomainEvents(): array;

    public function recordThat(ContractsDomainEvent $event): void;
}