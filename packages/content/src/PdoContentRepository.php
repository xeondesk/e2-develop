<?php
declare(strict_types=1);

namespace Nexo\Content;

use Nexo\Database\Connection;
use Nexo\Database\PdoRepository;
use Nexo\Database\TransactionManager;
use Nexo\Domain\Result;

final class PdoContentRepository extends PdoRepository implements ContentRepository
{
    protected function tableName(): string
    {
        return 'nexo_content_entries';
    }

    protected function entityClass(): string
    {
        return ContentEntry::class;
    }

    protected function mapRow(array $row): object
    {
        $contentId = new ContentId($row['id']);
        $data = json_decode($row['data'], true) ?? [];
        $status = ContentStatus::from($row['status']);
        
        $entry = new ContentEntry(
            $contentId,
            $row['type_id'],
            $data,
            $status,
            $row['created_by'] ?? null
        );

        // We need to hydrate revisions and publication
        // This is a simplified version - in reality we'd load them separately
        return $entry;
    }

    protected function mapEntity(object $entity): array
    {
        if (!$entity instanceof ContentEntry) {
            throw new \InvalidArgumentException('Entity must be a ContentEntry');
        }

        return [
            'id' => (string) $entity->contentId(),
            'type_id' => $entity->typeId(),
            'data' => json_encode($entity->data()),
            'status' => $entity->status()->value,
            'revision_number' => $entity->revisionNumber(),
            'created_at' => $entity->createdAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $entity->updatedAt()->format(\DateTimeInterface::ATOM),
            'created_by' => $entity->createdBy(),
            'updated_by' => $entity->updatedBy(),
        ];
    }

    public function findById(ContentId $id): ?ContentEntry
    {
        return $this->find((string) $id);
    }

    public function findByType(string $typeId): iterable
    {
        return $this->findBy(['type_id' => $typeId]);
    }

    public function findByStatus(ContentStatus $status): iterable
    {
        return $this->findBy(['status' => $status->value]);
    }

    /** @return Revision[] */
    public function findRevisions(ContentId $contentId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM nexo_content_revisions WHERE content_id = :id ORDER BY number ASC"
        );
        $stmt->execute([':id' => (string) $contentId]);
        $revisions = [];
        while ($row = $stmt->fetch()) {
            $revisions[] = Revision::fromArray($row);
        }
        return $revisions;
    }

    public function findRevision(ContentId $contentId, int $number): ?Revision
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM nexo_content_revisions WHERE content_id = :id AND number = :number"
        );
        $stmt->execute([':id' => (string) $contentId, ':number' => $number]);
        $row = $stmt->fetch();
        return $row ? Revision::fromArray($row) : null;
    }

    public function findPublication(ContentId $contentId): ?Publication
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM nexo_content_publications WHERE content_id = :id"
        );
        $stmt->execute([':id' => (string) $contentId]);
        $row = $stmt->fetch();
        return $row ? Publication::fromArray($row) : null;
    }

    public function saveRevision(Revision $revision): void
    {
        $this->transactionManager->run(function () use ($revision) {
            $data = $revision->toArray();
            $stmt = $this->connection->prepare(
                "INSERT INTO nexo_content_revisions (content_id, number, data, created_at, author_id, message) 
                 VALUES (:content_id, :number, :data, :created_at, :author_id, :message)
                 ON CONFLICT (content_id, number) DO UPDATE SET
                    data = EXCLUDED.data,
                    created_at = EXCLUDED.created_at,
                    author_id = EXCLUDED.author_id,
                    message = EXCLUDED.message"
            );
            $stmt->execute([
                ':content_id' => $data['content_id'],
                ':number' => $data['number'],
                ':data' => json_encode($data['data']),
                ':created_at' => $data['created_at'],
                ':author_id' => $data['author_id'],
                ':message' => $data['message'],
            ]);
        });
    }

    public function savePublication(Publication $publication): void
    {
        $this->transactionManager->run(function () use ($publication) {
            $data = $publication->toArray();
            $stmt = $this->connection->prepare(
                "INSERT INTO nexo_content_publications (content_id, revision_number, published_at, published_by, unpublished_at, unpublished_by) 
                 VALUES (:content_id, :revision_number, :published_at, :published_by, :unpublished_at, :unpublished_by)
                 ON CONFLICT (content_id) DO UPDATE SET
                    revision_number = EXCLUDED.revision_number,
                    published_at = EXCLUDED.published_at,
                    published_by = EXCLUDED.published_by,
                    unpublished_at = EXCLUDED.unpublished_at,
                    unpublished_by = EXCLUDED.unpublished_by"
            );
            $stmt->execute([
                ':content_id' => $data['content_id'],
                ':revision_number' => $data['revision_number'],
                ':published_at' => $data['published_at'],
                ':published_by' => $data['published_by'],
                ':unpublished_at' => $data['unpublished_at'],
                ':unpublished_by' => $data['unpublished_by'],
            ]);
        });
    }

    public function save(ContentEntry $entry): void
    {
        $this->transactionManager->run(function () use ($entry) {
            // Save the main entry
            parent::save($entry);
            
            // Save new revisions
            foreach ($entry->pullDomainEvents() as $event) {
                // Domain events are handled by the application layer
            }
            
            // Save publication if exists
            if ($entry->publication()) {
                $this->savePublication($entry->publication());
            }
            
            // Save latest revision
            $this->saveRevision($entry->latestRevision());
        });
    }
}