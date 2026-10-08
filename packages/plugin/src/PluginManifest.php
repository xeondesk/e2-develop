<?php
declare(strict_types=1);

namespace Nexo\Plugin;

final class PluginManifest
{
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly string $description,
        public readonly string $class,
        public readonly string $nexoVersionConstraint = '*',
        public readonly array $capabilities = []
    ) {}

    public static function fromArray(array $data): self
    {
        foreach (['name', 'version', 'class'] as $key) {
            if (empty($data[$key]) || !is_string($data[$key])) {
                throw new \InvalidArgumentException("Plugin manifest missing required key '{$key}'");
            }
        }
        return new self(
            $data['name'],
            $data['version'],
            $data['description'] ?? '',
            $data['class'],
            $data['nexo_version'] ?? '*',
            $data['capabilities'] ?? []
        );
    }

    public function isCompatibleWith(string $nexoVersion): bool
    {
        if ($this->nexoVersionConstraint === '*') {
            return true;
        }
        return version_compare($nexoVersion, $this->nexoVersionConstraint, '>=');
    }
}
