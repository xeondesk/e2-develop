<?php
declare(strict_types=1);

namespace Nexo\Domain;

/**
 * @template T
 */
final class Result
{
    /** @var mixed */
    private mixed $value;

    /** @var \Throwable|null */
    private ?\Throwable $error;

    private bool $isSuccess;

    private function __construct(mixed $value = null, ?\Throwable $error = null)
    {
        $this->value = $value;
        $this->error = $error;
        $this->isSuccess = $error === null;
    }

    /** @template T
     * @param T $value
     * @return Result<T>
     */
    public static function ok(mixed $value = null): self
    {
        return new self($value);
    }

    public static function err(\Throwable $error): self
    {
        return new self(null, $error);
    }

    public function isOk(): bool
    {
        return $this->isSuccess;
    }

    public function isErr(): bool
    {
        return !$this->isSuccess;
    }

    /**
     * @return mixed
     */
    public function unwrap(): mixed
    {
        if ($this->isErr()) {
            throw $this->error;
        }
        return $this->value;
    }

    /**
     * @param T $default
     * @return T|mixed
     */
    public function unwrapOr(mixed $default): mixed
    {
        return $this->isOk() ? $this->value : $default;
    }

    public function unwrapErr(): ?\Throwable
    {
        return $this->error;
    }

    /** @template U
     * @param callable(mixed): U $fn
     * @return Result<U>
     */
    public function map(callable $fn): self
    {
        if ($this->isErr()) {
            return $this;
        }
        try {
            return self::ok($fn($this->value));
        } catch (\Throwable $e) {
            return self::err($e);
        }
    }

    /** @template U
     * @param callable(mixed): Result<U> $fn
     * @return Result<U>
     */
    public function flatMap(callable $fn): self
    {
        if ($this->isErr()) {
            return $this;
        }
        try {
            return $fn($this->value);
        } catch (\Throwable $e) {
            return self::err($e);
        }
    }

    /** @template U
     * @param callable(\Throwable): U $fn
     * @return Result<U>
     */
    public function mapErr(callable $fn): self
    {
        if ($this->isOk()) {
            return $this;
        }
        try {
            return self::ok($fn($this->error));
        } catch (\Throwable $e) {
            return self::err($e);
        }
    }
}