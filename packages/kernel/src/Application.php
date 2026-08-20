<?php
declare(strict_types=1);

namespace Nexo\Kernel;

use Nexo\Container\Container;
use Nexo\Container\ContainerInterface;
use Nexo\Container\ServiceProvider;

class Application
{
    private Container $container;

    /** @var ServiceProvider[] */
    private array $providers = [];

    private bool $booted = false;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container ?? new Container();
        $this->registerCoreServices();
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot($this->container);
        }

        $this->booted = true;
    }

    public function register(ServiceProvider $provider): void
    {
        $provider->register($this->container);
        $this->providers[] = $provider;
    }

    private function registerCoreServices(): void
    {
        $this->container->singleton('app', fn () => $this);
        $this->container->singleton(ContainerInterface::class, fn () => $this->container);
        $this->container->singleton(Application::class, fn () => $this);
    }
}