<?php

namespace App\Core;

/**
 * Config — Loads .env and provides get/set access to all config values.
 */
class Config
{
    private static array $config = [];
    private static bool  $loaded = false;

    /** Load .env file from project root */
    public static function load(string $envPath): void
    {
        if (self::$loaded) return;

        if (!file_exists($envPath)) {
            throw new \RuntimeException(".env file not found at: {$envPath}");
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Strip carriage returns (Windows \r\n line endings uploaded via Hostinger)
            $line = trim(str_replace("\r", '', $line));
            if ($line === '' || str_starts_with($line, '#')) continue;

            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key   = trim(str_replace("\r", '', $key));
            $value = trim(str_replace("\r", '', $value), " \t\"\\'");
            if ($key === '') continue;

            self::$config[$key] = $value;
            $_ENV[$key]         = $value;
            putenv("{$key}={$value}");
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::$config[$key] = $value;
    }

    public static function all(): array
    {
        return self::$config;
    }
}
