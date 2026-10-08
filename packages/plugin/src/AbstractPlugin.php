<?php
declare(strict_types=1);

namespace Nexo\Plugin;

use Nexo\Container\Container;

abstract class AbstractPlugin implements Plugin
{
    abstract public function manifest(): PluginManifest;

    public function register(Container $container): void
    {
    }

    public function boot(Container $container): void
    {
    }
}
