<?php
declare(strict_types=1);

namespace Nexo\Api;

use Nexo\Container\Container;
use Nexo\Container\ContainerInterface;
use Nexo\Container\ServiceProvider;
use Nexo\Config\Config;
use Nexo\Config\Configuration;
use Nexo\Config\Environment;
use Nexo\Content\ContentServiceProvider;
use Nexo\Events\EventsServiceProvider;
use Nexo\Kernel\Application as KernelApplication;
use Nexo\Schema\SchemaServiceProvider;
use Nexo\Identity\IdentityServiceProvider;
use Nexo\Authorization\AuthorizationServiceProvider;
use Nexo\Workflow\WorkflowServiceProvider;
use Nexo\Observability\ObservabilityServiceProvider;

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
        $this->getContainer()->singleton(Config::class, fn () => $config);
        $this->getContainer()->singleton('config', fn () => $config);

        $this->register(new SchemaServiceProvider());
        $this->register(new EventsServiceProvider());
        $this->register(new ContentServiceProvider());
        $this->register(new IdentityServiceProvider());
        $this->register(new AuthorizationServiceProvider());
        $this->register(new WorkflowServiceProvider());
        $this->register(new ObservabilityServiceProvider());

        $pluginsPath = realpath(dirname(__DIR__, 3) . '/plugins') ?: null;
        if ($pluginsPath !== null) {
            (new \Nexo\Plugin\PluginRuntime($pluginsPath, $this->version()))->load($this->getContainer());
        }
    }

    public function registerProvider(ServiceProvider $provider): void
    {
        $this->register($provider);
    }
}