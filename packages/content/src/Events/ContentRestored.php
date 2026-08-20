<?php
declare(strict_types=1);

namespace Nexo\Content\Events;

use Nexo\Contracts\DomainEvent;
use Nexo\Content\ContentId;

final readonly class ContentRestored implements DomainEvent
{
    public function __construct(
        private ContentId $contentId,
        private ?string $authorId = null
    ) {}

    public function eventId(): string { return 'content_restored_' . uniqid('', true); }
    public function eventName(): string { return 'content.restored'; }
    public function eventVersion(): int { return 1; }
    public function occurredAt(): \DateTimeImmutable { return new \DateTimeImmutable(); }
    public function actorId(): ?string { return $this->authorId; }
    public function tenantId(): ?string { return null; }
    public function correlationId(): ?string { return (string) $this->contentId; }
    public function causationId(): ?string
    {
        return null;
    }

    public function payload(): array { return ['content_id' => (string) $this->contentId]; }
}