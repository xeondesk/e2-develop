<?php
declare(strict_types=1);

namespace Nexo\Schema;

use Nexo\Config\Config;
use Nexo\Database\Connection;
use Nexo\Database\ConnectionFactory;
use Nexo\Database\PdoTransactionManager;
use Nexo\Contracts\TransactionManager;
use Nexo\Container\Container;
use Nexo\Container\ServiceProvider;

final class SchemaServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(FieldRegistry::class, fn () => new FieldRegistry());
        
        $container->singleton(SchemaValidator::class, function (Container $c) {
            return new SchemaValidator($c->get(FieldRegistry::class));
        });

        $container->factory(Connection::class, function (Container $c) {
            $config = $c->get(Config::class);
            $factory = new ConnectionFactory($config);
            return $factory->create();
        });

        $container->singleton(TransactionManager::class, function (Container $c) {
            return new PdoTransactionManager($c->get(Connection::class));
        });

        $container->singleton(SchemaRepository::class, function (Container $c) {
            return new SchemaRepository($c->get(Connection::class), $c->get(TransactionManager::class));
        });
    }

    public function boot(Container $container): void
    {
    }
}