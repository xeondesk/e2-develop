<?php
declare(strict_types=1);

namespace Nexo\Workflow;

use Nexo\Authorization\AuthorizationChecker;
use Nexo\Domain\Result;
use Nexo\Events\EventDispatcher;
use Nexo\Identity\Audit\AuditEvent;
use Nexo\Identity\Audit\AuditLogger;
use Nexo\Workflow\Events\WorkflowTransitioned;

final class WorkflowEngine
{
    public function __construct(
        private ?AuthorizationChecker $checker = null,
        private ?EventDispatcher $events = null,
        private ?AuditLogger $audit = null
    ) {}

    public function canTransition(WorkflowDefinition $definition, string $currentState, string $transitionName, ?string $actorId = null): bool
    {
        if ($definition->transitionTarget($currentState, $transitionName) === null) {
            return false;
        }
        $permission = $definition->transitionPermission($transitionName);
        if ($permission !== null && $actorId !== null && $this->checker !== null) {
            return $this->checker->can($actorId, $permission);
        }
        return true;
    }

    public function transition(WorkflowDefinition $definition, string $currentState, string $transitionName, string $resourceId, ?string $actorId = null): Result
    {
        $target = $definition->transitionTarget($currentState, $transitionName);
        if ($target === null) {
            return Result::err(new \DomainException("Transition '{$transitionName}' is not allowed from state '{$currentState}'"));
        }

        $permission = $definition->transitionPermission($transitionName);
        if ($permission !== null && $actorId !== null && $this->checker !== null) {
            try {
                $allowed = $this->checker->can($actorId, $permission);
            } catch (\Throwable) {
                $allowed = false;
            }
            if (!$allowed) {
                return Result::err(new \DomainException("Actor '{$actorId}' lacks permission '{$permission}'"));
            }
        }

        $event = new WorkflowTransitioned($resourceId, $definition->name(), $transitionName, $currentState, $target, $actorId);
        $this->events?->dispatch($event);
        $this->audit?->log(AuditEvent::record($actorId ?? 'system', "workflow.{$transitionName}", 'workflow', $resourceId, $event->payload()));

        return Result::ok($target);
    }
}
