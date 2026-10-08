<?php
declare(strict_types=1);

namespace Nexo\Content;

use Nexo\Config\Config;
use Nexo\Database\Connection;
use Nexo\Database\ConnectionFactory;
use Nexo\Database\PdoTransactionManager;
use Nexo\Contracts\TransactionManager;
use Nexo\Container\Container;
use Nexo\Container\ServiceProvider;

final class ContentServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        // The Connection, TransactionManager, and SchemaRepository are already registered
        // by SchemaServiceProvider and DatabaseServiceProvider
        
        $container->singleton(ContentRepository::class, function (Container $c) {
            return new PdoContentRepository(
                $c->get(Connection::class),
                $c->get(TransactionManager::class)
            );
        });
    }

    public function boot(Container $container): void
    {
    }
}