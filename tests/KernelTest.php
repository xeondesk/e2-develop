<?php
declare(strict_types=1);

namespace Nexo\Tests;

use PHPUnit\Framework\TestCase;
use Nexo\Kernel\Application;

final class KernelTest extends TestCase
{
    public function testVersion(): void
    {
        self::assertSame('0.1.0', (new Application())->version());
    }
}
