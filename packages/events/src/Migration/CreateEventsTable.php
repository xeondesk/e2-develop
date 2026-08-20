<?php
declare(strict_types=1);

namespace Nexo\Events\Migration;

use Nexo\Database\Connection;
use Nexo\Database\Migration\Migration;

final class CreateEventsTable implements Migration
{
    public function version(): string
    {
        return '20240101000003';
    }

    public function name(): string
    {
        return 'create_events_table';
    }

    public function up(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS nexo_events (
                event_id VARCHAR(100) PRIMARY KEY,
                event_name VARCHAR(255) NOT NULL,
                event_version INTEGER NOT NULL DEFAULT 1,
                occurred_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                actor_id VARCHAR(100),
                tenant_id VARCHAR(100),
                correlation_id VARCHAR(100),
                causation_id VARCHAR(100),
                payload JSONB NOT NULL DEFAULT '{}'::jsonb
            );
            CREATE INDEX IF NOT EXISTS idx_nexo_events_name ON nexo_events(event_name);
            CREATE INDEX IF NOT EXISTS idx_nexo_events_correlation ON nexo_events(correlation_id);
            CREATE INDEX IF NOT EXISTS idx_nexo_events_actor ON nexo_events(actor_id);
            CREATE INDEX IF NOT EXISTS idx_nexo_events_tenant ON nexo_events(tenant_id);
            CREATE INDEX IF NOT EXISTS idx_nexo_events_occurred ON nexo_events(occurred_at);
        SQL;
        $connection->exec($sql);
    }

    public function down(Connection $connection): void
    {
        $connection->exec('DROP TABLE IF EXISTS nexo_events');
    }
}