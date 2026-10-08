<?php
declare(strict_types=1);

namespace Nexo\Identity\Migration;

use Nexo\Database\Connection;
use Nexo\Database\Migration\Migration;

final class CreateIdentityTables implements Migration
{
    public function version(): string
    {
        return '20240101000004';
    }

    public function name(): string
    {
        return 'create_identity_tables';
    }

    public function up(Connection $connection): void
    {
        $connection->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS nexo_users (
                id UUID PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'active',
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );
            CREATE INDEX IF NOT EXISTS idx_nexo_users_email ON nexo_users(email);

            CREATE TABLE IF NOT EXISTS nexo_audit_events (
                id UUID PRIMARY KEY,
                actor_id VARCHAR(100),
                action VARCHAR(120) NOT NULL,
                resource_type VARCHAR(120) NOT NULL,
                resource_id VARCHAR(100),
                metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
                occurred_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );
            CREATE INDEX IF NOT EXISTS idx_nexo_audit_actor ON nexo_audit_events(actor_id);
            CREATE INDEX IF NOT EXISTS idx_nexo_audit_resource ON nexo_audit_events(resource_type, resource_id);
        SQL);
    }

    public function down(Connection $connection): void
    {
        $connection->exec('DROP TABLE IF EXISTS nexo_audit_events');
        $connection->exec('DROP TABLE IF EXISTS nexo_users');
    }
}
