<?php
declare(strict_types=1);

namespace Nexo\Schema\Migration;

use Nexo\Database\Connection;
use Nexo\Database\Migration\Migration;

final class CreateSchemasTable implements Migration
{
    public function version(): string
    {
        return '20240101000001';
    }

    public function name(): string
    {
        return 'create_schemas_table';
    }

    public function up(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS nexo_schemas (
                schema_id VARCHAR(100) PRIMARY KEY,
                version INTEGER NOT NULL,
                label VARCHAR(255) NOT NULL,
                description TEXT,
                fields JSONB NOT NULL DEFAULT '[]'::jsonb,
                indexes JSONB NOT NULL DEFAULT '[]'::jsonb,
                metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );
            CREATE INDEX IF NOT EXISTS idx_nexo_schemas_label ON nexo_schemas(label);
        SQL;
        $connection->exec($sql);
    }

    public function down(Connection $connection): void
    {
        $connection->exec('DROP TABLE IF EXISTS nexo_schemas');
    }
}