<?php
declare(strict_types=1);

namespace Nexo\Plugins\Hello;

use Nexo\Container\Container;
use Nexo\Plugin\AbstractPlugin;
use Nexo\Plugin\PluginManifest;

final class HelloPlugin extends AbstractPlugin
{
    public function manifest(): PluginManifest
    {
        return PluginManifest::fromArray(json_decode(file_get_contents(__DIR__ . '/../nexo-plugin.json'), true));
    }

    public function boot(Container $container): void
    {
        $container->set('hello', 'world');
    }
}
