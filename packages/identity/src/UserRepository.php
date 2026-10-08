<?php
declare(strict_types=1);

namespace Nexo\Identity;

use Nexo\Contracts\Repository;

interface UserRepository extends Repository
{
    public function findByEmail(string $email): ?User;

    public function findById(string $id): ?User;
}
