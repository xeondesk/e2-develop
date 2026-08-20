<?php
declare(strict_types=1);

namespace Nexo\Schema;

final class SchemaVersion
{
    private int $version;

    public function __construct(int $version)
    {
        if ($version < 1) {
            throw new \InvalidArgumentException('Schema version must be >= 1');
        }
        $this->version = $version;
    }

    public function value(): int
    {
        return $this->version;
    }

    public function next(): self
    {
        return new self($this->version + 1);
    }

    public function equals(self $other): bool
    {
        return $this->version === $other->version;
    }

    public function __toString(): string
    {
        return (string) $this->version;
    }

    public static function fromInt(int $version): self
    {
        return new self($version);
    }
}