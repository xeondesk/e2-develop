<?php
declare(strict_types=1);

namespace Nexo\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @implements ContainerInterface<string, mixed>
 */
final class Container implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $services = [];

    /** @var array<string, callable(Container): mixed> */
    private array $factories = [];

    /** @var array<string, bool> */
    private array $resolving = [];

    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }

    public function get(string $id): mixed
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new NotFoundException("Service '$id' not found");
        }

        if (isset($this->resolving[$id])) {
            throw new ContainerException("Circular dependency detected for '$id'");
        }

        $this->resolving[$id] = true;

        try {
            $factory = $this->factories[$id];
            $service = $factory($this);
            $this->services[$id] = $service;
            unset($this->factories[$id], $this->resolving[$id]);
            return $service;
        } catch (\Throwable $e) {
            unset($this->resolving[$id]);
            throw $e;
        }
    }

    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
        unset($this->factories[$id]);
    }

    public function factory(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->services[$id]);
    }

    public function singleton(string $id, callable $factory): void
    {
        $this->factory($id, function (Container $container) use ($factory, $id) {
            $service = $factory($container);
            $this->services[$id] = $service;
            return $service;
        });
    }

    public function extend(string $id, callable $decorator): void
    {
        if (!isset($this->factories[$id]) && !isset($this->services[$id])) {
            throw new NotFoundException("Cannot extend unknown service '$id'");
        }

        $originalFactory = $this->factories[$id] ?? fn ($c) => $this->services[$id];

        $this->factory($id, function (Container $container) use ($originalFactory, $decorator) {
            $service = $originalFactory($container);
            return $decorator($service, $container);
        });
    }
}

class ContainerException extends \RuntimeException implements ContainerExceptionInterface {}

class NotFoundException extends \RuntimeException implements NotFoundExceptionInterface {}