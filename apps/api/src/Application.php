<?php
declare(strict_types=1);

namespace Nexo\Api;

use Nexo\Container\Container;
use Nexo\Container\ContainerInterface;
use Nexo\Container\ServiceProvider;
use Nexo\Config\Configuration;
use Nexo\Config\Environment;
use Nexo\Kernel\Application as KernelApplication;

final class Application extends KernelApplication
{
    public function __construct(?ContainerInterface $container = null)
    {
        parent::__construct($container);
        $this->configureServices();
    }

    private function configureServices(): void
    {
        Environment::load();

        $config = new Configuration([
            'app.env' => Environment::getString('APP_ENV', 'local'),
            'app.debug' => Environment::getBool('APP_DEBUG', true),
            'app.url' => Environment::getString('APP_URL', 'http://localhost:8080'),
            'db.dsn' => Environment::getString('DB_DSN', 'pgsql:host=localhost;port=5432;dbname=nexo'),
            'db.user' => Environment::getString('DB_USER', 'nexo'),
            'db.password' => Environment::getString('DB_PASSWORD', 'nexo'),
        ]);

        $this->getContainer()->singleton(Configuration::class, fn () => $config);
        $this->getContainer()->singleton('config', fn () => $config);
    }

    public function registerProvider(ServiceProvider $provider): void
    {
        $this->register($provider);
    }
}