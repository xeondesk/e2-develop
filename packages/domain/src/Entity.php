<?php
declare(strict_types=1);

namespace Nexo\Domain;

interface Entity
{
    public function id(): string;

    public function equals(Entity $other): bool;
}