<?php
declare(strict_types=1);

namespace Nexo\Identity;

use Nexo\Database\PdoRepository;

final class PdoUserRepository extends PdoRepository implements UserRepository
{
    protected function tableName(): string
    {
        return 'nexo_users';
    }

    protected function entityClass(): string
    {
        return User::class;
    }

    protected function mapRow(array $row): object
    {
        $user = new User(
            $row['id'],
            $row['email'],
            $row['name'],
            $row['password_hash'],
            UserStatus::from($row['status'])
        );
        return $user;
    }

    protected function mapEntity(object $entity): array
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('Entity must be a User');
        }

        return [
            'id' => $entity->id(),
            'email' => $entity->email(),
            'name' => $entity->name(),
            'password_hash' => $entity->passwordHash(),
            'status' => $entity->status()->value,
            'created_at' => $entity->createdAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $entity->updatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->connection->prepare('SELECT * FROM nexo_users WHERE email = :email');
        $stmt->execute([':email' => strtolower($email)]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row) : null;
    }

    public function findById(string $id): ?User
    {
        $found = $this->find($id);
        return $found instanceof User ? $found : null;
    }
}
