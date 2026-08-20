<?php
declare(strict_types=1);

namespace Nexo\Config;

interface Config
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function all(): array;
}