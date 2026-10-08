<?php
declare(strict_types=1);

namespace Nexo\Authorization;

final class Role
{
    public function __construct(
        private string $id,
        private string $name,
        private string $description,
        /** @var string[] */
        private array $permissions
    ) {}

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
    public function description(): string { return $this->description; }
    /** @return string[] */
    public function permissions(): array { return $this->permissions; }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
