<?php
declare(strict_types=1);

namespace Nexo\Contracts;

interface TransactionManager
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;

    public function inTransaction(): bool;

    public function run(callable $callback): mixed;
}