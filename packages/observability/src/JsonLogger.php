<?php
declare(strict_types=1);

namespace Nexo\Observability;

use Psr\Log\AbstractLogger;
use Stringable;

final class JsonLogger extends AbstractLogger
{
    /** @var resource */
    private $stream;

    /** @param resource|null $stream */
    public function __construct($stream = null, private string $channel = 'nexo')
    {
        $this->stream = $stream ?? fopen('php://stderr', 'a');
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $record = [
            'ts' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'level' => (string) $level,
            'channel' => $this->channel,
            'message' => (string) $message,
            'context' => $context,
        ];
        fwrite($this->stream, json_encode($record, JSON_UNESCAPED_SLASHES) . "\n");
    }
}
