<?php
declare(strict_types=1);

namespace Nexo\Workflow;

use Nexo\Contracts\TransactionManager;
use Nexo\Database\Connection;

final class PdoWorkflowDefinitionRepository implements WorkflowDefinitionRepository
{
    public function __construct(
        private Connection $connection,
        private TransactionManager $transactionManager
    ) {}

    public function save(WorkflowDefinition $definition): void
    {
        $this->transactionManager->run(function () use ($definition) {
            $stmt = $this->connection->prepare(
                'INSERT INTO nexo_workflow_definitions (name, definition, created_at, updated_at)
                 VALUES (:name, :definition, NOW(), NOW())
                 ON CONFLICT (name) DO UPDATE SET definition = EXCLUDED.definition, updated_at = NOW()'
            );
            $stmt->execute([
                ':name' => $definition->name(),
                ':definition' => json_encode($definition->toArray()),
            ]);
        });
    }

    public function findByName(string $name): ?WorkflowDefinition
    {
        $stmt = $this->connection->prepare('SELECT definition FROM nexo_workflow_definitions WHERE name = :name');
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch();
        return $row ? WorkflowDefinition::fromArray(json_decode($row['definition'], true)) : null;
    }

    public function findAll(): array
    {
        $rows = $this->connection->query('SELECT definition FROM nexo_workflow_definitions ORDER BY name')->fetchAll();
        return array_map(fn ($row) => WorkflowDefinition::fromArray(json_decode($row['definition'], true)), $rows);
    }

    public function delete(string $name): void
    {
        $stmt = $this->connection->prepare('DELETE FROM nexo_workflow_definitions WHERE name = :name');
        $stmt->execute([':name' => $name]);
    }
}
