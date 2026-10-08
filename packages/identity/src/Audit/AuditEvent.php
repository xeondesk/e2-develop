<?php
declare(strict_types=1);

namespace Nexo\Identity\Audit;

final class AuditEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $actorId,
        public readonly string $action,
        public readonly string $resourceType,
        public readonly string $resourceId,
        public readonly array $metadata = [],
        public readonly ?\DateTimeImmutable $occurredAt = null
    ) {}

    public static function record(string $actorId, string $action, string $resourceType, string $resourceId, array $metadata = []): self
    {
        $id = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000, random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
        return new self($id, $actorId, $action, $resourceType, $resourceId, $metadata, new \DateTimeImmutable());
    }
}
