<?php
declare(strict_types=1);

namespace Nexo\Contracts;

interface Repository
{
    public function find(string $id): ?object;

    public function findAll(): iterable;

    public function save(object $entity): void;

    public function delete(string $id): void;

    public function exists(string $id): bool;
}