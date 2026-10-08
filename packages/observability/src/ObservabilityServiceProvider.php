<?php
declare(strict_types=1);

namespace Nexo\Observability;

use Nexo\Container\Container;
use Nexo\Container\ServiceProvider;
use Nexo\Database\Connection;
use Nexo\Database\Health\DatabaseHealthCheck;
use Psr\Log\LoggerInterface;

final class ObservabilityServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(LoggerInterface::class, fn () => new JsonLogger());

        $container->singleton(HealthRegistry::class, function (Container $c) {
            $registry = new HealthRegistry();
            $registry->register('database', function () use ($c) {
                return (new DatabaseHealthCheck($c->get(Connection::class)))->check();
            });
            return $registry;
        });
    }

    public function boot(Container $container): void
    {
    }
}
