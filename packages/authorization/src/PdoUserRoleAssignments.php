<?php
declare(strict_types=1);

namespace Nexo\Authorization;

use Nexo\Contracts\TransactionManager;
use Nexo\Database\Connection;

final class PdoUserRoleAssignments implements UserRoleAssignments
{
    public function __construct(
        private Connection $connection,
        private TransactionManager $transactionManager,
        private RoleRepository $roles
    ) {}

    public function assign(string $userId, string $roleId): void
    {
        $this->transactionManager->run(function () use ($userId, $roleId) {
            $stmt = $this->connection->prepare(
                'INSERT INTO nexo_user_roles (user_id, role_id) VALUES (:u, :r) ON CONFLICT DO NOTHING'
            );
            $stmt->execute([':u' => $userId, ':r' => $roleId]);
        });
    }

    public function revoke(string $userId, string $roleId): void
    {
        $this->transactionManager->run(function () use ($userId, $roleId) {
            $stmt = $this->connection->prepare(
                'DELETE FROM nexo_user_roles WHERE user_id = :u AND role_id = :r'
            );
            $stmt->execute([':u' => $userId, ':r' => $roleId]);
        });
    }

    public function rolesForUser(string $userId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT r.* FROM nexo_roles r INNER JOIN nexo_user_roles ur ON ur.role_id = r.id WHERE ur.user_id = :u'
        );
        $stmt->execute([':u' => $userId]);
        $roles = [];
        while ($row = $stmt->fetch()) {
            $roles[] = new Role($row['id'], $row['name'], $row['description'] ?? '', json_decode($row['permissions'], true) ?? []);
        }
        return $roles;
    }
}
