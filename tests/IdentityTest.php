<?php
declare(strict_types=1);

namespace Nexo\Tests;

use Nexo\Authorization\AuthorizationChecker;
use Nexo\Authorization\Role;
use Nexo\Authorization\UserRoleAssignments;
use Nexo\Identity\User;
use Nexo\Identity\UserStatus;
use PHPUnit\Framework\TestCase;

final class IdentityTest extends TestCase
{
    public function testUserPasswordVerification(): void
    {
        $user = User::create('bob@example.com', 'Bob', 'hunter2');

        self::assertTrue($user->verifyPassword('hunter2'));
        self::assertFalse($user->verifyPassword('wrong'));
        self::assertSame(UserStatus::ACTIVE, $user->status());
    }

    public function testUserRejectsInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new User('id', 'not-an-email', 'X', 'hash');
    }

    public function testAuthorizationChecker(): void
    {
        $assignments = new class () implements UserRoleAssignments {
            public array $map = [];
            public function assign(string $userId, string $roleId): void {}
            public function revoke(string $userId, string $roleId): void {}
            public function rolesForUser(string $userId): array
            {
                return [new Role('editor', 'editor', '', ['content.read', 'content.write'])];
            }
        };

        $checker = new AuthorizationChecker($assignments);
        self::assertTrue($checker->can('u1', 'content.write'));
        self::assertFalse($checker->can('u1', 'user.delete'));
        self::assertSame(['content.read', 'content.write'], $checker->permissionsFor('u1'));
    }
}
