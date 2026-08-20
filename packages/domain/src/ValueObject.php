<?php
declare(strict_types=1);

namespace Nexo\Domain;

interface ValueObject
{
    public function equals(ValueObject $other): bool;

    public function sameAs(ValueObject $other): bool;
}