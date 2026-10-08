<?php
declare(strict_types=1);

namespace Nexo\Workflow;

use Nexo\Authorization\AuthorizationChecker;
use Nexo\Container\Container;
use Nexo\Container\ServiceProvider;
use Nexo\Contracts\TransactionManager;
use Nexo\Database\Connection;
use Nexo\Events\EventDispatcher;
use Nexo\Identity\Audit\AuditLogger;

final class WorkflowServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(WorkflowDefinitionRepository::class, function (Container $c) {
            return new PdoWorkflowDefinitionRepository(
                $c->get(Connection::class),
                $c->get(TransactionManager::class)
            );
        });

        $container->singleton(WorkflowEngine::class, function (Container $c) {
            return new WorkflowEngine(
                $c->has(AuthorizationChecker::class) ? $c->get(AuthorizationChecker::class) : null,
                $c->has(EventDispatcher::class) ? $c->get(EventDispatcher::class) : null,
                $c->has(AuditLogger::class) ? $c->get(AuditLogger::class) : null
            );
        });
    }

    public function boot(Container $container): void
    {
    }
}
