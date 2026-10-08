<?php
declare(strict_types=1);

namespace Nexo\Identity\Audit;

use Nexo\Contracts\TransactionManager;
use Nexo\Database\Connection;

final class PdoAuditLogger implements AuditLogger
{
    public function __construct(
        private Connection $connection,
        private TransactionManager $transactionManager
    ) {}

    public function log(AuditEvent $event): void
    {
        $this->transactionManager->run(function () use ($event) {
            $stmt = $this->connection->prepare(
                'INSERT INTO nexo_audit_events (id, actor_id, action, resource_type, resource_id, metadata, occurred_at)
                 VALUES (:id, :actor_id, :action, :resource_type, :resource_id, :metadata, :occurred_at)'
            );
            $stmt->execute([
                ':id' => $event->id,
                ':actor_id' => $event->actorId,
                ':action' => $event->action,
                ':resource_type' => $event->resourceType,
                ':resource_id' => $event->resourceId,
                ':metadata' => json_encode($event->metadata),
                ':occurred_at' => ($event->occurredAt ?? new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ]);
        });
    }
}
