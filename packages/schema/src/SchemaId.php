<?php
declare(strict_types=1);

namespace Nexo\Schema;

final class SchemaId implements \Stringable
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function create(string $value): self
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $value)) {
            throw new \InvalidArgumentException("SchemaId must be lowercase alphanumeric with underscores, starting with a letter: {$value}");
        }
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}