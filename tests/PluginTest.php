<?php
declare(strict_types=1);

namespace Nexo\Tests;

use Nexo\Plugin\PluginManifest;
use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase
{
    public function testManifestParsing(): void
    {
        $m = PluginManifest::fromArray([
            'name' => 'hello',
            'version' => '0.1.0',
            'class' => 'Foo\\Bar\\Baz',
        ]);
        self::assertSame('hello', $m->name);
        self::assertSame('*', $m->nexoVersionConstraint);
        self::assertTrue($m->isCompatibleWith('9.9.9'));
    }

    public function testManifestRequiresName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PluginManifest::fromArray(['version' => '1.0.0', 'class' => 'X']);
    }

    public function testCompatibilityConstraint(): void
    {
        $m = new PluginManifest('x', '1.0.0', '', 'X', '0.2.0');
        self::assertFalse($m->isCompatibleWith('0.1.0'));
        self::assertTrue($m->isCompatibleWith('0.2.0'));
    }
}
