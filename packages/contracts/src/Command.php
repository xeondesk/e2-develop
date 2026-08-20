<?php
declare(strict_types=1);

namespace Nexo\Contracts;

interface Command
{
    public function commandId(): string;

    public function commandName(): string;

    public function payload(): array;
}