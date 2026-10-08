<?php
declare(strict_types=1);

namespace Nexo\Config;

interface Config
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function all(): array;

    public function getString(string $key, string $default = ''): string;

    public function getInt(string $key, int $default = 0): int;

    public function getBool(string $key, bool $default = false): bool;

    public function getFloat(string $key, float $default = 0.0): float;

    public function getArray(string $key, array $default = []): array;
}