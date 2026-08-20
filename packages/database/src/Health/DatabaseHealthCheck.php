<?php
declare(strict_types=1);

namespace Nexo\Database\Health;

use Nexo\Database\Connection;

final class DatabaseHealthCheck
{
    public function __construct(private Connection $connection) {}

    public function check(): array
    {
        try {
            $start = microtime(true);
            $stmt = $this->connection->query('SELECT 1');
            $stmt->fetch();
            $latency = microtime(true) - $start;

            return [
                'healthy' => true,
                'latency_ms' => round($latency * 1000, 2),
                'driver' => $this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME),
                'server_version' => $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}