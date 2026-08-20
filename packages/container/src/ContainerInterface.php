<?php
declare(strict_types=1);

namespace Nexo\Container;

interface ContainerInterface
{
    public function has(string $id): bool;

    public function get(string $id): mixed;

    public function set(string $id, mixed $service): void;
}