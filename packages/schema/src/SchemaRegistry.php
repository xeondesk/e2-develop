<?php
declare(strict_types=1);

namespace Nexo\Schema;

final class SchemaRegistry
{
    /** @var array<string, array<string,mixed>> */
    private array $schemas = [];

    public function register(string $name, array $schema): void
    {
        $this->schemas[$name] = $schema;
    }

    public function get(string $name): ?array
    {
        return $this->schemas[$name] ?? null;
    }
}
