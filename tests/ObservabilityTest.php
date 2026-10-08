<?php
declare(strict_types=1);

namespace Nexo\Tests;

use Nexo\Observability\HealthRegistry;
use Nexo\Observability\JsonLogger;
use PHPUnit\Framework\TestCase;

final class ObservabilityTest extends TestCase
{
    public function testJsonLoggerWritesJsonLine(): void
    {
        $stream = fopen('php://temp', 'r+');
        $logger = new JsonLogger($stream, 'test');
        $logger->info('hello', ['k' => 'v']);
        rewind($stream);
        $line = fgets($stream);
        $record = json_decode($line, true);
        self::assertSame('hello', $record['message']);
        self::assertSame('test', $record['channel']);
        self::assertSame('v', $record['context']['k']);
    }

    public function testHealthRegistryAggregates(): void
    {
        $registry = new HealthRegistry();
        $registry->register('ok', fn () => ['healthy' => true]);
        $registry->register('bad', fn () => ['healthy' => false]);
        $result = $registry->check();
        self::assertFalse($result['healthy']);
        self::assertArrayHasKey('ok', $result['checks']);
        self::assertArrayHasKey('bad', $result['checks']);
    }

    public function testHealthRegistryCatchesExceptions(): void
    {
        $registry = new HealthRegistry();
        $registry->register('boom', fn () => throw new \RuntimeException('kaput'));
        $result = $registry->check();
        self::assertFalse($result['healthy']);
        self::assertSame('kaput', $result['checks']['boom']['error']);
    }
}
