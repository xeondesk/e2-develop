<?php
declare(strict_types=1);

namespace Nexo\Events;

use Nexo\Container\Container;
use Nexo\Container\ServiceProvider;

final class EventsServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(EventDispatcher::class, fn () => new EventDispatcher());
        
        $container->singleton(EventStore::class, function (Container $c) {
            return new PdoEventStore(
                $c->get(\Nexo\Database\Connection::class),
                $c->get(\Nexo\Database\TransactionManager::class)
            );
        });
    }

    public function boot(Container $container): void
    {
        // Add logging middleware if logger is available
        if ($container->has(\Psr\Log\LoggerInterface::class)) {
            $dispatcher = $container->get(EventDispatcher::class);
            $dispatcher->addMiddleware(new LoggingMiddleware($container->get(\Psr\Log\LoggerInterface::class)));
        }
    }
}