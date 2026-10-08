<?php
declare(strict_types=1);

namespace Nexo\Workflow\Migration;

use Nexo\Database\Connection;
use Nexo\Database\Migration\Migration;

final class CreateWorkflowsTable implements Migration
{
    public function version(): string
    {
        return '20240101000006';
    }

    public function name(): string
    {
        return 'create_workflows_table';
    }

    public function up(Connection $connection): void
    {
        $connection->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS nexo_workflow_definitions (
                name VARCHAR(120) PRIMARY KEY,
                definition JSONB NOT NULL,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );
        SQL);
    }

    public function down(Connection $connection): void
    {
        $connection->exec('DROP TABLE IF EXISTS nexo_workflow_definitions');
    }
}
