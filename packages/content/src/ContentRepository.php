<?php
declare(strict_types=1);

namespace Nexo\Content;

use Nexo\Contracts\Repository;

interface ContentRepository extends Repository
{
    public function findById(ContentId $id): ?ContentEntry;

    public function findByType(string $typeId): iterable;

    public function findByStatus(ContentStatus $status): iterable;

    /** @return Revision[] */
    public function findRevisions(ContentId $contentId): array;

    public function findRevision(ContentId $contentId, int $number): ?Revision;

    public function findPublication(ContentId $contentId): ?Publication;

    public function saveRevision(Revision $revision): void;

    public function savePublication(Publication $publication): void;
}