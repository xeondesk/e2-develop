<?php
declare(strict_types=1);

namespace Nexo\Workflow\Events;

use Nexo\Contracts\DomainEvent;

final readonly class WorkflowTransitioned implements DomainEvent
{
    public function __construct(
        private string $resourceId,
        private string $workflowName,
        private string $transition,
        private string $from,
        private string $to,
        private ?string $actorId = null
    ) {}

    public function eventId(): string { return 'workflow_transitioned_' . uniqid('', true); }
    public function eventName(): string { return 'workflow.transitioned'; }
    public function eventVersion(): int { return 1; }
    public function occurredAt(): \DateTimeImmutable { return new \DateTimeImmutable(); }
    public function actorId(): ?string { return $this->actorId; }
    public function tenantId(): ?string { return null; }
    public function correlationId(): ?string { return $this->resourceId; }
    public function causationId(): ?string { return null; }

    public function payload(): array
    {
        return [
            'resource_id' => $this->resourceId,
            'workflow' => $this->workflowName,
            'transition' => $this->transition,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
