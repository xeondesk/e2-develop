<?php
declare(strict_types=1);

namespace Nexo\Content;

use Nexo\Domain\ValueObject;

final class Publication implements ValueObject, \Stringable
{
    private ContentId $contentId;
    private int $revisionNumber;
    private \DateTimeImmutable $publishedAt;
    private ?string $publishedBy;
    private ?\DateTimeImmutable $unpublishedAt = null;
    private ?string $unpublishedBy = null;

    public function __construct(
        ContentId $contentId,
        int $revisionNumber,
        \DateTimeImmutable $publishedAt,
        ?string $publishedBy = null
    ) {
        $this->contentId = $contentId;
        $this->revisionNumber = $revisionNumber;
        $this->publishedAt = $publishedAt;
        $this->publishedBy = $publishedBy;
    }

    public function contentId(): ContentId
    {
        return $this->contentId;
    }

    public function revisionNumber(): int
    {
        return $this->revisionNumber;
    }

    public function publishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function publishedBy(): ?string
    {
        return $this->publishedBy;
    }

    public function isPublished(): bool
    {
        return $this->unpublishedAt === null;
    }

    public function unpublish(\DateTimeImmutable $at, ?string $by = null): void
    {
        $this->unpublishedAt = $at;
        $this->unpublishedBy = $by;
    }

    public function unpublishedAt(): ?\DateTimeImmutable
    {
        return $this->unpublishedAt;
    }

    public function unpublishedBy(): ?string
    {
        return $this->unpublishedBy;
    }

    public function __toString(): string
    {
        return "Publication of {$this->contentId} at revision {$this->revisionNumber}";
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }
        return $this->contentId->equals($other->contentId)
            && $this->revisionNumber === $other->revisionNumber;
    }

    public function sameAs(ValueObject $other): bool
    {
        return $this === $other || $this->equals($other);
    }

    public function toArray(): array
    {
        return [
            'content_id' => (string) $this->contentId,
            'revision_number' => $this->revisionNumber,
            'published_at' => $this->publishedAt->format(\DateTimeInterface::ATOM),
            'published_by' => $this->publishedBy,
            'unpublished_at' => $this->unpublishedAt?->format(\DateTimeInterface::ATOM),
            'unpublished_by' => $this->unpublishedBy,
        ];
    }

    public static function fromArray(array $data): self
    {
        $publication = new self(
            new ContentId($data['content_id']),
            $data['revision_number'],
            new \DateTimeImmutable($data['published_at']),
            $data['published_by'] ?? null
        );
        if (isset($data['unpublished_at']) && $data['unpublished_at']) {
            $publication->unpublish(
                new \DateTimeImmutable($data['unpublished_at']),
                $data['unpublished_by'] ?? null
            );
        }
        return $publication;
    }
}