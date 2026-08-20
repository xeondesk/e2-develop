<?php
declare(strict_types=1);

namespace Nexo\Config;

final class Environment
{
    /**
     * @param array<string, string> $defaults
     */
    public static function load(string $path = null, array $defaults = []): void
    {
        $file = $path ?? __DIR__ . '/../../../../.env';

        if (!is_file($file)) {
            foreach ($defaults as $key => $value) {
                if (getenv($key) === false) {
                    putenv("$key=$value");
                }
            }
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (getenv($key) === false) {
                    putenv("$key=$value");
                }
            }
        }

        foreach ($defaults as $key => $value) {
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        return (int) $value;
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        return (float) $value;
    }

    public static function getString(string $key, string $default = ''): string
    {
        $value = self::get($key);
        return $value ?? $default;
    }

    public static function has(string $key): bool
    {
        return getenv($key) !== false;
    }
}