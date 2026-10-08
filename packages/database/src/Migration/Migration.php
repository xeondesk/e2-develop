<?php
declare(strict_types=1);

namespace Nexo\Database\Migration;
use Nexo\Database\Connection;


interface Migration
{
    public function version(): string;

    public function name(): string;

    public function up(Connection $connection): void;

    public function down(Connection $connection): void;
}