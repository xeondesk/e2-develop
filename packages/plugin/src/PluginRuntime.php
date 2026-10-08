<?php
declare(strict_types=1);

namespace Nexo\Plugin;

use Nexo\Container\Container;

final class PluginRuntime
{
    /** @var Plugin[] */
    private array $plugins = [];

    public function __construct(private string $path, private string $nexoVersion = '0.1.0') {}

    /** @return Plugin[] */
    public function load(Container $container): array
    {
        if (!is_dir($this->path)) {
            return [];
        }

        foreach (glob($this->path . '/*/nexo-plugin.json') ?: [] as $manifestPath) {
            $manifest = PluginManifest::fromArray(json_decode(file_get_contents($manifestPath), true) ?? []);

            if (!$manifest->isCompatibleWith($this->nexoVersion)) {
                throw new \RuntimeException("Plugin '{$manifest->name}' requires Nexo {$manifest->nexoVersionConstraint}, running {$this->nexoVersion}");
            }

            $class = $manifest->class;
            if (!class_exists($class)) {
                throw new \RuntimeException("Plugin class '{$class}' not found for '{$manifest->name}'");
            }

            $plugin = new $class();
            $plugin->register($container);
            $plugin->boot($container);
            $this->plugins[$manifest->name] = $plugin;
        }

        return $this->plugins;
    }

    /** @return Plugin[] */
    public function plugins(): array
    {
        return $this->plugins;
    }
}
