<?php
declare(strict_types=1);

namespace Nexo\Authorization;

final class AuthorizationChecker
{
    public function __construct(private UserRoleAssignments $assignments)
    {
    }

    public function can(string $userId, string $permission): bool
    {
        foreach ($this->assignments->rolesForUser($userId) as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /** @return string[] */
    public function permissionsFor(string $userId): array
    {
        $permissions = [];
        foreach ($this->assignments->rolesForUser($userId) as $role) {
            foreach ($role->permissions() as $permission) {
                $permissions[$permission] = true;
            }
        }
        return array_keys($permissions);
    }
}
