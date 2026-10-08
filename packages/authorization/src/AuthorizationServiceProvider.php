<?php
declare(strict_types=1);

namespace Nexo\Authorization;

use Nexo\Container\Container;
use Nexo\Container\ServiceProvider;
use Nexo\Contracts\TransactionManager;
use Nexo\Database\Connection;

final class AuthorizationServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(RoleRepository::class, function (Container $c) {
            return new PdoRoleRepository(
                $c->get(Connection::class),
                $c->get(TransactionManager::class)
            );
        });

        $container->singleton(UserRoleAssignments::class, function (Container $c) {
            return new PdoUserRoleAssignments(
                $c->get(Connection::class),
                $c->get(TransactionManager::class),
                $c->get(RoleRepository::class)
            );
        });

        $container->singleton(AuthorizationChecker::class, function (Container $c) {
            return new AuthorizationChecker($c->get(UserRoleAssignments::class));
        });
    }

    public function boot(Container $container): void
    {
    }
}
