<?php
declare(strict_types=1);

namespace Nexo\Observability;

final class HealthRegistry
{
    /** @var array<string, callable> */
    private array $checks = [];

    public function register(string $name, callable $check): void
    {
        $this->checks[$name] = $check;
    }

    public function check(): array
    {
        $results = [];
        $healthy = true;
        foreach ($this->checks as $name => $check) {
            try {
                $result = $check();
            } catch (\Throwable $e) {
                $result = ['healthy' => false, 'error' => $e->getMessage()];
            }
            $results[$name] = $result;
            $healthy = $healthy && ($result['healthy'] ?? false);
        }
        return ['healthy' => $healthy, 'checks' => $results];
    }
}
