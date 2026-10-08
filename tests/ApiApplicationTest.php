<?php
declare(strict_types=1);

namespace Nexo\Tests;

use Nexo\Api\Application;
use Nexo\Config\Config;
use Nexo\Config\Configuration;
use PHPUnit\Framework\TestCase;

final class ApiApplicationTest extends TestCase
{
    public function testBootsAndBindsCoreServices(): void
    {
        $app = new Application();
        $app->boot();

        $container = $app->getContainer();
        self::assertInstanceOf(Configuration::class, $container->get(Configuration::class));
        self::assertInstanceOf(Config::class, $container->get(Config::class));
        self::assertInstanceOf(Configuration::class, $container->get('config'));
    }
}
