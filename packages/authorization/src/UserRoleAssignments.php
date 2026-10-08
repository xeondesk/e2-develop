<?php
declare(strict_types=1);

namespace Nexo\Authorization;

interface UserRoleAssignments
{
    public function assign(string $userId, string $roleId): void;

    public function revoke(string $userId, string $roleId): void;

    /** @return Role[] */
    public function rolesForUser(string $userId): array;
}
