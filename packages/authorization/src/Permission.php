<?php
declare(strict_types=1);

namespace Nexo\Authorization;

final class Permission
{
    public function __construct(public readonly string $name)
    {
        if (!preg_match('/^[a-z0-9][a-z0-9-]*(\.[a-z0-9][a-z0-9-]*)+$/', $name)) {
            throw new \InvalidArgumentException("Invalid permission name: {$name}");
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
