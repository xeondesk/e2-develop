<?php
declare(strict_types=1);

namespace Nexo\Contracts;

interface Query
{
    public function queryName(): string;

    public function payload(): array;
}