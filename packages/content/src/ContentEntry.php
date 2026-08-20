<?php
declare(strict_types=1);

namespace Nexo\Content;

use Nexo\Content\Events\ContentPublished;
use Nexo\Content\Events\ContentUnpublished;
use Nexo\Content\Events\ContentArchived;
use Nexo\Content\Events\ContentTrashed;
use Nexo\Content\Events\ContentRestored;
use Nexo\Contracts\DomainEvent;
use Nexo\Domain\AbstractAggregateRoot;
use Nexo\Domain\Result;

final class ContentEntry extends AbstractAggregateRoot
{
    private ContentId $id;
    private string $typeId;
    private array $data;
    private ContentStatus $status;
    private int $revisionNumber = 0;
    /** @var Revision[] */
    private array $revisions = [];
    private ?Publication $publication = null;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    private ?string $createdBy;
    private ?string $updatedBy;

    public function __construct(
        ContentId $id,
        string $typeId,
        array $data,
        ContentStatus $status = ContentStatus::DRAFT,
        ?string $createdBy = null
    ) {
        $this->id = $id;
        $this->typeId = $typeId;
        $this->data = $data;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->createdBy = $createdBy;
        $this->updatedBy = $createdBy;

        $this->recordInitialRevision($createdBy);
    }

    private function recordInitialRevision(?string $authorId): void
    {
        $this->revisionNumber = 1;
        $revision = new Revision(
            $this->id,
            1,
            $this->data,
            $this->createdAt,
            $authorId,
            'Initial creation'
        );
        $this->revisions[] = $revision;
    }

    public function id(): string
    {
        return (string) $this->id;
    }

    public function contentId(): ContentId
    {
        return $this->id;
    }

    public function typeId(): string
    {
        return $this->typeId;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function status(): ContentStatus
    {
        return $this->status;
    }

    public function revisionNumber(): int
    {
        return $this->revisionNumber;
    }

    /** @return Revision[] */
    public function revisions(): array
    {
        return $this->revisions;
    }

    public function latestRevision(): Revision
    {
        return end($this->revisions);
    }

    public function publication(): ?Publication
    {
        return $this->publication;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function createdBy(): ?string
    {
        return $this->createdBy;
    }

    public function updatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function update(array $data, ?string $updatedBy = null, string $message = 'Content updated'): Result
    {
        if (!$this->status->isEditable()) {
            return Result::err(new \DomainException("Cannot update content in status: {$this->status->value}"));
        }

        $this->data = array_merge($this->data, $data);
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedBy = $updatedBy;
        $this->revisionNumber++;
        
        $revision = new Revision(
            $this->id,
            $this->revisionNumber,
            $this->data,
            $this->updatedAt,
            $updatedBy,
            $message
        );
        $this->revisions[] = $revision;

        return Result::ok($this);
    }

    public function publish(?string $publishedBy = null): Result
    {
        if ($this->status === ContentStatus::PUBLISHED) {
            return Result::err(new \DomainException('Content is already published'));
        }

        if ($this->status === ContentStatus::TRASHED) {
            return Result::err(new \DomainException('Cannot publish trashed content'));
        }

        $this->status = ContentStatus::PUBLISHED;
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedBy = $publishedBy;

        $this->publication = new Publication(
            $this->id,
            $this->revisionNumber,
            $this->updatedAt,
            $publishedBy
        );

        $this->recordThat(new ContentPublished($this->id, $this->revisionNumber, $publishedBy));

        return Result::ok($this);
    }

    public function unpublish(?string $unpublishedBy = null): Result
    {
        if ($this->status !== ContentStatus::PUBLISHED) {
            return Result::err(new \DomainException('Only published content can be unpublished'));
        }

        if (!$this->publication) {
            return Result::err(new \DomainException('No publication record found'));
        }

        $this->status = ContentStatus::DRAFT;
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedBy = $unpublishedBy;

        $this->publication->unpublish($this->updatedAt, $unpublishedBy);

        $this->recordThat(new ContentUnpublished($this->id, $unpublishedBy));

        return Result::ok($this);
    }

    public function archive(?string $archivedBy = null): Result
    {
        if ($this->status === ContentStatus::TRASHED) {
            return Result::err(new \DomainException('Cannot archive trashed content'));
        }

        $this->status = ContentStatus::ARCHIVED;
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedBy = $archivedBy;

        $this->recordThat(new ContentArchived($this->id, $archivedBy));

        return Result::ok($this);
    }

    public function trash(?string $trashedBy = null): Result
    {
        $this->status = ContentStatus::TRASHED;
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedBy = $trashedBy;

        $this->recordThat(new ContentTrashed($this->id, $trashedBy));

        return Result::ok($this);
    }

    public function restore(?string $restoredBy = null): Result
    {
        if ($this->status !== ContentStatus::TRASHED) {
            return Result::err(new \DomainException('Only trashed content can be restored'));
        }

        $this->status = ContentStatus::DRAFT;
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedBy = $restoredBy;

        $this->recordThat(new ContentRestored($this->id, $restoredBy));

        return Result::ok($this);
    }

    public function equals(\Nexo\Domain\Entity $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }
        return $this->id->equals($other->id);
    }
}