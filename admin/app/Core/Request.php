<?php

namespace App\Core;

/**
 * Request — Encapsulates the HTTP request.
 * Provides sanitized access to GET, POST, FILES, SERVER, and route params.
 */
class Request
{
    private array $params = [];

    public function getMethod(): string
    {
        // Allow method override via POST _method field
        if ($this->isPost() && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function isPost(): bool  { return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
    public function isGet(): bool   { return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'GET'; }
    public function isAjax(): bool  { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }

    /** Get a sanitized GET param */
    public function query(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    /** Get a sanitized POST param */
    public function input(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }

    /** Get raw POST (avoid for HTML — use input() instead) */
    public function raw(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /** Get all POST params sanitized */
    public function all(): array
    {
        return array_map(fn($v) => $this->sanitize($v), $_POST);
    }

    /** Get only the specified POST keys (missing keys return null) */
    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = array_key_exists($key, $_POST)
                ? $this->sanitize($_POST[$key])
                : null;
        }
        return $result;
    }

    /** Get uploaded file */
    public function file(string $key): array|null
    {
        return $_FILES[$key] ?? null;
    }

    /** Route parameters (e.g. {id}) */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /** Get a header value */
    public function header(string $key): string|null
    {
        $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$normalized] ?? null;
    }

    /** Check if request expects JSON */
    public function wantsJson(): bool
    {
        return str_contains($this->header('Accept') ?? '', 'application/json')
            || $this->isAjax();
    }

    /** Sanitize a single value (XSS protection) */
    private function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn($v) => $this->sanitize($v), $value);
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Get IP address */
    public function ip(): string
    {
        return $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }
}
