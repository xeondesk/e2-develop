<?php
declare(strict_types=1);

namespace Nexo\Authorization;

use Nexo\Contracts\Repository;

interface RoleRepository extends Repository
{
    public function findByName(string $name): ?Role;
}
