<?php

namespace App\Core;

/**
 * Base Controller — shared render, redirect, JSON response, and flash methods.
 */
abstract class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Render a view file with data.
     *
     * @param string $view   Dot-notation path relative to /views (e.g. 'dashboard.index')
     * @param array  $data   Variables to extract into the view scope
     * @param string $layout Layout to use (default: 'main')
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = ROOT_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$viewPath}");
        }

        // Capture view content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Wrap in layout
        $layoutPath = ROOT_PATH . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /** Send a JSON response */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Redirect to a URL */
    protected function redirect(string $url): void
    {
        http_response_code(302);
        header("Location: {$url}");
        exit;
    }

    /** Redirect back to the previous page */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/dashboard');
        $this->redirect($referer);
    }

    /** Set a flash message */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /** Check if user is authenticated */
    protected function isAuthenticated(): bool
    {
        return !empty($_SESSION['user']);
    }

    /** Get current authenticated user */
    protected function user(): array|null
    {
        return $_SESSION['user'] ?? null;
    }

    /** Check if current user has a given role */
    protected function hasRole(string $role): bool
    {
        return ($_SESSION['user']['role'] ?? '') === $role;
    }
}
