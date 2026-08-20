<?php
declare(strict_types=1);

namespace Nexo\Contracts;

interface Clock
{
    public function now(): \DateTimeImmutable;

    public function today(): \DateTimeImmutable;
}