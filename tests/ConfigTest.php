<?php
declare(strict_types=1);

namespace Nexo\Tests;

use Nexo\Config\Configuration;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testTypedGetters(): void
    {
        $config = new Configuration([
            'app.name' => 'nexo',
            'app.port' => 8080,
            'app.debug' => true,
            'app.ratio' => 1.5,
        ]);

        self::assertSame('nexo', $config->getString('app.name'));
        self::assertSame(8080, $config->getInt('app.port'));
        self::assertTrue($config->getBool('app.debug'));
        self::assertSame(1.5, $config->getFloat('app.ratio'));
    }
}
