<?php
declare(strict_types=1);

namespace Nexo\Domain;

class DomainException extends \DomainException
{
    /** @var array<string, mixed> */
    private array $context = [];

    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /** @param array<string, mixed> $context */
    public static function withContext(string $message, array $context = [], int $code = 0, ?\Throwable $previous = null): self
    {
        $exception = new self($message, $code, $previous);
        $exception->context = $context;
        return $exception;
    }

    /** @return array<string, mixed> */
    public function context(): array
    {
        return $this->context;
    }

    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }
}