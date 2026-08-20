<?php
declare(strict_types=1);

namespace Nexo\Content\Migration;

use Nexo\Database\Connection;
use Nexo\Database\Migration\Migration;

final class CreateContentTables implements Migration
{
    public function version(): string
    {
        return '20240101000002';
    }

    public function name(): string
    {
        return 'create_content_tables';
    }

    public function up(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS nexo_content_entries (
                id UUID PRIMARY KEY,
                type_id VARCHAR(100) NOT NULL,
                data JSONB NOT NULL DEFAULT '{}'::jsonb,
                status VARCHAR(40) NOT NULL DEFAULT 'draft',
                revision_number INTEGER NOT NULL DEFAULT 1,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                created_by VARCHAR(100),
                updated_by VARCHAR(100)
            );
            CREATE INDEX IF NOT EXISTS idx_nexo_content_type ON nexo_content_entries(type_id);
            CREATE INDEX IF NOT EXISTS idx_nexo_content_status ON nexo_content_entries(status);
            CREATE INDEX IF NOT EXISTS idx_nexo_content_created ON nexo_content_entries(created_at);

            CREATE TABLE IF NOT EXISTS nexo_content_revisions (
                content_id UUID NOT NULL REFERENCES nexo_content_entries(id) ON DELETE CASCADE,
                number INTEGER NOT NULL,
                data JSONB NOT NULL,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                author_id VARCHAR(100),
                message TEXT,
                PRIMARY KEY (content_id, number)
            );
            CREATE INDEX IF NOT EXISTS idx_nexo_content_revisions_content ON nexo_content_revisions(content_id);

            CREATE TABLE IF NOT EXISTS nexo_content_publications (
                content_id UUID PRIMARY KEY REFERENCES nexo_content_entries(id) ON DELETE CASCADE,
                revision_number INTEGER NOT NULL,
                published_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                published_by VARCHAR(100),
                unpublished_at TIMESTAMPTZ,
                unpublished_by VARCHAR(100)
            );
        SQL;
        $connection->exec($sql);
    }

    public function down(Connection $connection): void
    {
        $connection->exec('DROP TABLE IF EXISTS nexo_content_publications');
        $connection->exec('DROP TABLE IF EXISTS nexo_content_revisions');
        $connection->exec('DROP TABLE IF EXISTS nexo_content_entries');
    }
}