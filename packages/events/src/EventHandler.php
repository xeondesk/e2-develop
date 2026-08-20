<?php
declare(strict_types=1);

namespace Nexo\Events;

use Nexo\Contracts\Event;

interface EventHandler
{
    public function handle(Event $event): void;

    public function supports(string $eventName): bool;

    public function priority(): int;
}