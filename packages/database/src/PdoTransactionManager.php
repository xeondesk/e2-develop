<?php
declare(strict_types=1);

namespace Nexo\Database;

use Nexo\Contracts\TransactionManager;

final class PdoTransactionManager implements TransactionManager
{
    private bool $inTransaction = false;
    private int $nestingLevel = 0;

    public function __construct(private Connection $connection) {}

    public function begin(): void
    {
        if ($this->nestingLevel === 0) {
            $this->connection->beginTransaction();
            $this->inTransaction = true;
        }
        $this->nestingLevel++;
    }

    public function commit(): void
    {
        if ($this->nestingLevel <= 0) {
            throw new \RuntimeException('No active transaction to commit');
        }

        $this->nestingLevel--;

        if ($this->nestingLevel === 0) {
            $this->connection->commit();
            $this->inTransaction = false;
        }
    }

    public function rollback(): void
    {
        if ($this->nestingLevel <= 0) {
            throw new \RuntimeException('No active transaction to rollback');
        }

        $this->nestingLevel--;

        if ($this->nestingLevel === 0) {
            $this->connection->rollBack();
            $this->inTransaction = false;
        }
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function run(callable $callback): mixed
    {
        $this->begin();
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}