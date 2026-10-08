<?php
declare(strict_types=1);

namespace Nexo\Plugin;

use Nexo\Container\Container;

interface Plugin
{
    public function manifest(): PluginManifest;

    public function register(Container $container): void;

    public function boot(Container $container): void;
}
