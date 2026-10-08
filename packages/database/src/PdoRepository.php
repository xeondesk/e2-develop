<?php
declare(strict_types=1);

namespace Nexo\Database;

use Nexo\Contracts\Repository;
use Nexo\Domain\Identifier;
use Nexo\Contracts\TransactionManager;

abstract class PdoRepository implements Repository
{
    public function __construct(
        protected Connection $connection,
        protected TransactionManager $transactionManager
    ) {}

    abstract protected function tableName(): string;

    abstract protected function entityClass(): string;

    abstract protected function mapRow(array $row): object;

    abstract protected function mapEntity(object $entity): array;

    public function find(string $id): ?object
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tableName()} WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRow($row) : null;
    }

    public function findAll(): iterable
    {
        $stmt = $this->connection->query("SELECT * FROM {$this->tableName()}");
        while ($row = $stmt->fetch()) {
            yield $this->mapRow($row);
        }
    }

    public function save(object $entity): void
    {
        $data = $this->mapEntity($entity);
        $id = $data['id'] ?? null;

        if ($id && $this->exists($id)) {
            $this->update($entity, $data);
        } else {
            $this->insert($entity, $data);
        }
    }

    protected function insert(object $entity, array $data): void
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn ($c) => ":{$c}", $columns);
        $sql = "INSERT INTO {$this->tableName()} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($data);
    }

    protected function update(object $entity, array $data): void
    {
        $id = $data['id'];
        unset($data['id']);

        if (empty($data)) {
            return;
        }

        $setClause = implode(', ', array_map(fn ($c) => "{$c} = :{$c}", array_keys($data)));
        $sql = "UPDATE {$this->tableName()} SET {$setClause} WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($data);
    }

    public function delete(string $id): void
    {
        $stmt = $this->connection->prepare("DELETE FROM {$this->tableName()} WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function exists(string $id): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT 1 FROM {$this->tableName()} WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function count(): int
    {
        $stmt = $this->connection->query("SELECT COUNT(*) FROM {$this->tableName()}");
        return (int) $stmt->fetchColumn();
    }

    public function findBy(array $criteria): iterable
    {
        if (empty($criteria)) {
            return $this->findAll();
        }

        $whereClause = implode(' AND ', array_map(fn ($k) => "{$k} = :{$k}", array_keys($criteria)));
        $sql = "SELECT * FROM {$this->tableName()} WHERE {$whereClause}";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($criteria);

        while ($row = $stmt->fetch()) {
            yield $this->mapRow($row);
        }
    }

    public function findOneBy(array $criteria): ?object
    {
        foreach ($this->findBy($criteria) as $entity) {
            return $entity;
        }
        return null;
    }
}