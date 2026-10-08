<?php
declare(strict_types=1);

namespace Nexo\Authorization\Migration;

use Nexo\Database\Connection;
use Nexo\Database\Migration\Migration;

final class CreateAuthorizationTables implements Migration
{
    public function version(): string
    {
        return '20240101000005';
    }

    public function name(): string
    {
        return 'create_authorization_tables';
    }

    public function up(Connection $connection): void
    {
        $connection->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS nexo_roles (
                id VARCHAR(100) PRIMARY KEY,
                name VARCHAR(120) NOT NULL UNIQUE,
                description TEXT,
                permissions JSONB NOT NULL DEFAULT '[]'::jsonb
            );

            CREATE TABLE IF NOT EXISTS nexo_user_roles (
                user_id UUID NOT NULL REFERENCES nexo_users(id) ON DELETE CASCADE,
                role_id VARCHAR(100) NOT NULL REFERENCES nexo_roles(id) ON DELETE CASCADE,
                PRIMARY KEY (user_id, role_id)
            );
        SQL);
    }

    public function down(Connection $connection): void
    {
        $connection->exec('DROP TABLE IF EXISTS nexo_user_roles');
        $connection->exec('DROP TABLE IF EXISTS nexo_roles');
    }
}
