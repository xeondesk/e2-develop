<?php
declare(strict_types=1);

namespace Nexo\Http;

final class HttpResponse
{
    public function __construct(
        public readonly int $status,
        public readonly mixed $data,
        public readonly array $headers = ['Content-Type: application/json']
    ) {}

    public static function json(mixed $data, int $status = 200): self
    {
        return new self($status, $data);
    }

    public static function error(string $code, string $message, int $status): self
    {
        return new self($status, ['error' => ['code' => $code, 'message' => $message]]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $header) {
            header($header);
        }
        echo json_encode($this->data, JSON_PRETTY_PRINT);
    }
}
