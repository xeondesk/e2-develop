<?php
declare(strict_types=1);

namespace Nexo\Authorization;

use Nexo\Database\PdoRepository;

final class PdoRoleRepository extends PdoRepository implements RoleRepository
{
    protected function tableName(): string
    {
        return 'nexo_roles';
    }

    protected function entityClass(): string
    {
        return Role::class;
    }

    protected function mapRow(array $row): object
    {
        return new Role(
            $row['id'],
            $row['name'],
            $row['description'] ?? '',
            json_decode($row['permissions'], true) ?? []
        );
    }

    protected function mapEntity(object $entity): array
    {
        if (!$entity instanceof Role) {
            throw new \InvalidArgumentException('Entity must be a Role');
        }

        return [
            'id' => $entity->id(),
            'name' => $entity->name(),
            'description' => $entity->description(),
            'permissions' => json_encode($entity->permissions()),
        ];
    }

    public function findByName(string $name): ?Role
    {
        $stmt = $this->connection->prepare('SELECT * FROM nexo_roles WHERE name = :name');
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row) : null;
    }
}
