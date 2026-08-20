<?php
declare(strict_types=1);

namespace Nexo\Events;

interface DomainEventSubscriber
{
    /** @return array<string, string|array{method: string, priority: int}> */
    public static function getSubscribedEvents(): array;
}