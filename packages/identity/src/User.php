<?php
declare(strict_types=1);

namespace Nexo\Identity;

use Nexo\Domain\Entity;

final class User implements Entity
{
    private string $id;
    private string $email;
    private string $name;
    private string $passwordHash;
    private UserStatus $status;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $email,
        string $name,
        string $passwordHash,
        UserStatus $status = UserStatus::ACTIVE
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email: {$email}");
        }
        $this->id = $id;
        $this->email = strtolower($email);
        $this->name = $name;
        $this->passwordHash = $passwordHash;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public static function create(string $email, string $name, string $plainPassword): self
    {
        $id = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000, random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
        return new self($id, $email, $name, password_hash($plainPassword, PASSWORD_DEFAULT));
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    public function activate(): void
    {
        $this->status = UserStatus::ACTIVE;
        $this->touch();
    }

    public function disable(): void
    {
        $this->status = UserStatus::DISABLED;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): string { return $this->id; }
    public function email(): string { return $this->email; }
    public function name(): string { return $this->name; }
    public function passwordHash(): string { return $this->passwordHash; }
    public function status(): UserStatus { return $this->status; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function equals(Entity $other): bool
    {
        return $other instanceof self && $other->id() === $this->id;
    }
}
