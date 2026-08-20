<?php
declare(strict_types=1);

namespace Nexo\Database;

use Nexo\Config\Config;
use PDO;

final class ConnectionFactory
{
    public function __construct(private Config $config) {}

    public function create(): Connection
    {
        $dsn = $this->config->getString('db.dsn', 'pgsql:host=localhost;port=5432;dbname=nexo');
        $user = $this->config->getString('db.user', 'nexo');
        $password = $this->config->getString('db.password', 'nexo');

        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return new Connection($pdo);
    }
}