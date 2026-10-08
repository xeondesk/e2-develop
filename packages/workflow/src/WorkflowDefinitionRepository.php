<?php
declare(strict_types=1);

namespace Nexo\Workflow;

interface WorkflowDefinitionRepository
{
    public function save(WorkflowDefinition $definition): void;

    public function findByName(string $name): ?WorkflowDefinition;

    /** @return WorkflowDefinition[] */
    public function findAll(): array;

    public function delete(string $name): void;
}
