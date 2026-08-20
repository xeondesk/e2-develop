<?php
declare(strict_types=1);

namespace Nexo\Content;

enum ContentStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case TRASHED = 'trashed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::PUBLISHED], true);
    }
}