<?php
declare(strict_types=1);

namespace Nexo\Database\Migration;

use Nexo\Database\Connection;
use Nexo\Contracts\TransactionManager;

final class MigrationRunner
{
    public function __construct(
        private Connection $connection,
        private TransactionManager $transactionManager,
        private MigrationRepository $repository
    ) {}

    public function migrate(array $migrations, ?string $targetVersion = null): array
    {
        $executed = [];
        $pending = $this->getPendingMigrations($migrations, $targetVersion);

        foreach ($pending as $migration) {
            $this->transactionManager->run(function () use ($migration, &$executed) {
                $start = microtime(true);
                $migration->up($this->connection);
                $duration = microtime(true) - $start;

                $this->repository->record($migration->version(), $migration->name(), $duration);
                $executed[] = $migration->version();
            });
        }

        return $executed;
    }

    public function rollback(array $migrations, int $steps = 1): array
    {
        $executed = $this->repository->getExecuted();
        $rolledBack = [];

        for ($i = 0; $i < $steps && !empty($executed); $i++) {
            $version = array_pop($executed);
            $migration = $this->findMigration($migrations, $version);

            if (!$migration) {
                throw new \RuntimeException("Migration {$version} not found for rollback");
            }

            $this->transactionManager->run(function () use ($migration, &$rolledBack) {
                $migration->down($this->connection);
                $this->repository->delete($version);
                $rolledBack[] = $version;
            });
        }

        return $rolledBack;
    }

    public function status(array $migrations): array
    {
        $executed = $this->repository->getExecuted();
        $executedMap = array_flip($executed);

        $status = [];
        foreach ($migrations as $migration) {
            $version = $migration->version();
            $status[] = [
                'version' => $version,
                'name' => $migration->name(),
                'executed' => isset($executedMap[$version]),
            ];
        }

        return $status;
    }

    /**
     * @return Migration[]
     */
    private function getPendingMigrations(array $migrations, ?string $targetVersion): array
    {
        $executed = $this->repository->getExecuted();
        $executedMap = array_flip($executed);

        $pending = [];
        foreach ($migrations as $migration) {
            if (isset($executedMap[$migration->version()])) {
                continue;
            }
            if ($targetVersion !== null && $migration->version() > $targetVersion) {
                break;
            }
            $pending[] = $migration;
        }

        usort($pending, fn ($a, $b) => $a->version() <=> $b->version());
        return $pending;
    }

    private function findMigration(array $migrations, string $version): ?Migration
    {
        foreach ($migrations as $migration) {
            if ($migration->version() === $version) {
                return $migration;
            }
        }
        return null;
    }
}