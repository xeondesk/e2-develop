<?php
declare(strict_types=1);

namespace Nexo\Identity\Audit;

interface AuditLogger
{
    public function log(AuditEvent $event): void;
}
