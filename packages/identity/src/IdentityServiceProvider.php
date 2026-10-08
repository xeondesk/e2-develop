<?php
declare(strict_types=1);

namespace Nexo\Identity;

use Nexo\Container\Container;
use Nexo\Container\ServiceProvider;
use Nexo\Contracts\TransactionManager;
use Nexo\Database\Connection;
use Nexo\Identity\Audit\AuditLogger;
use Nexo\Identity\Audit\PdoAuditLogger;

final class IdentityServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(UserRepository::class, function (Container $c) {
            return new PdoUserRepository(
                $c->get(Connection::class),
                $c->get(TransactionManager::class)
            );
        });

        $container->singleton(AuditLogger::class, function (Container $c) {
            return new PdoAuditLogger(
                $c->get(Connection::class),
                $c->get(TransactionManager::class)
            );
        });
    }

    public function boot(Container $container): void
    {
    }
}
