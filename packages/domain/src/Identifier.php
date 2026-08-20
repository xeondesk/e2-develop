<?php
declare(strict_types=1);

namespace Nexo\Domain;

abstract class Identifier implements \Stringable
{
    private string $value;

    final public function __construct(string $value)
    {
        $this->value = $value;
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

    public function sameAs(self $other): bool
    {
        return $this === $other || $this->equals($other);
    }
}