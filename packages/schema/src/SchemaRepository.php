<?php
declare(strict_types=1);

namespace Nexo\Schema;

use Nexo\Database\Connection;
use Nexo\Database\PdoRepository;

final class SchemaRepository extends PdoRepository
{
    protected function tableName(): string
    {
        return 'nexo_schemas';
    }

    protected function entityClass(): string
    {
        return Schema::class;
    }

    protected function mapRow(array $row): object
    {
        return Schema::fromArray([
            'id' => $row['schema_id'],
            'version' => (int) $row['version'],
            'label' => $row['label'],
            'description' => $row['description'] ?? '',
            'fields' => json_decode($row['fields'], true) ?? [],
            'indexes' => json_decode($row['indexes'], true) ?? [],
            'metadata' => json_decode($row['metadata'], true) ?? [],
        ]);
    }

    protected function mapEntity(object $entity): array
    {
        if (!$entity instanceof Schema) {
            throw new \InvalidArgumentException('Entity must be a Schema');
        }

        $data = $entity->toArray();
        return [
            'schema_id' => $data['id'],
            'version' => $data['version'],
            'label' => $data['label'],
            'description' => $data['description'],
            'fields' => json_encode($data['fields']),
            'indexes' => json_encode($data['indexes']),
            'metadata' => json_encode($data['metadata']),
        ];
    }

    public function findById(string $schemaId): ?Schema
    {
        return $this->find($schemaId);
    }

    /** @return Schema[] */
    public function findAll(): array
    {
        $schemas = [];
        foreach (parent::findAll() as $schema) {
            $schemas[] = $schema;
        }
        return $schemas;
    }

    public function findLatestVersion(string $schemaId): ?Schema
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tableName()} WHERE schema_id = :id ORDER BY version DESC LIMIT 1"
        );
        $stmt->execute([':id' => $schemaId]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row) : null;
    }

    public function save(Schema $schema): void
    {
        $this->transactionManager->run(function () use ($schema) {
            $this->insertOrUpdate($schema);
        });
    }

    private function insertOrUpdate(Schema $schema): void
    {
        $data = $this->mapEntity($schema);
        $id = $data['schema_id'];

        if ($this->exists($id)) {
            $this->update($schema, $data);
        } else {
            $this->insert($schema, $data);
        }
    }
}