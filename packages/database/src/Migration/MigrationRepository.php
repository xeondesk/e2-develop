<?php
declare(strict_types=1);

namespace Nexo\Database\Migration;

use Nexo\Database\Connection;

final class MigrationRepository
{
    private const TABLE = 'nexo_migrations';

    public function __construct(private Connection $connection) {}

    public function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
            version VARCHAR(50) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            checksum VARCHAR(64) NOT NULL,
            executed_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
            execution_time_ms DOUBLE PRECISION NOT NULL
        )";
        $this->connection->exec($sql);
    }

    public function record(string $version, string $name, float $duration): void
    {
        $this->ensureTable();

        $checksum = hash('sha256', $version . $name);
        $durationMs = round($duration * 1000, 3);

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " (version, name, checksum, execution_time_ms) VALUES (:version, :name, :checksum, :duration)"
        );
        $stmt->execute([
            ':version' => $version,
            ':name' => $name,
            ':checksum' => $checksum,
            ':duration' => $durationMs,
        ]);
    }

    public function delete(string $version): void
    {
        $stmt = $this->connection->prepare("DELETE FROM " . self::TABLE . " WHERE version = :version");
        $stmt->execute([':version' => $version]);
    }

    /**
     * @return string[]
     */
    public function getExecuted(): array
    {
        $this->ensureTable();

        $stmt = $this->connection->query("SELECT version FROM " . self::TABLE . " ORDER BY version");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * @return array<string, mixed>[]
     */
    public function getExecutedWithDetails(): array
    {
        $this->ensureTable();

        $stmt = $this->connection->query("SELECT * FROM " . self::TABLE . " ORDER BY version");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}