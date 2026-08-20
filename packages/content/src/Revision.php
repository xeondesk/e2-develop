<?php
declare(strict_types=1);

namespace Nexo\Content;

use Nexo\Domain\Identifier;
use Nexo\Domain\ValueObject;

final class Revision implements ValueObject, \Stringable
{
    private ContentId $contentId;
    private int $number;
    private array $data;
    private \DateTimeImmutable $createdAt;
    private ?string $authorId;
    private string $message;

    public function __construct(
        ContentId $contentId,
        int $number,
        array $data,
        \DateTimeImmutable $createdAt,
        ?string $authorId = null,
        string $message = ''
    ) {
        $this->contentId = $contentId;
        $this->number = $number;
        $this->data = $data;
        $this->createdAt = $createdAt;
        $this->authorId = $authorId;
        $this->message = $message;
    }

    public function contentId(): ContentId
    {
        return $this->contentId;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function authorId(): ?string
    {
        return $this->authorId;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        return "Revision #{$this->number} of {$this->contentId}";
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }
        return $this->contentId->equals($other->contentId)
            && $this->number === $other->number;
    }

    public function sameAs(ValueObject $other): bool
    {
        return $this === $other || $this->equals($other);
    }

    public function toArray(): array
    {
        return [
            'content_id' => (string) $this->contentId,
            'number' => $this->number,
            'data' => $this->data,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'author_id' => $this->authorId,
            'message' => $this->message,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new ContentId($data['content_id']),
            $data['number'],
            $data['data'],
            new \DateTimeImmutable($data['created_at']),
            $data['author_id'] ?? null,
            $data['message'] ?? ''
        );
    }
}