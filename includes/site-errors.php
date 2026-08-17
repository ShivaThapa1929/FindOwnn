<?php
/**
 * Public website error handling — user-friendly pages, internal logging.
 */

function site_is_debug(): bool
{
    static $debug = null;
    if ($debug !== null) {
        return $debug;
    }

    if (!class_exists(\App\Core\Config::class, false)) {
        $configFile = dirname(__DIR__) . '/admin/app/Core/Config.php';
        if (is_file($configFile)) {
            require_once $configFile;
            $envFile = dirname(__DIR__) . '/admin/.env';
            if (is_file($envFile)) {
                \App\Core\Config::load($envFile);
            }
        }
    }

    if (!class_exists(\App\Core\Config::class, false)) {
        $debug = false;
        return $debug;
    }

    $debug = filter_var(\App\Core\Config::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    return $debug;
}

function site_log_error(string $message, array $context = []): void
{
    $line = date('Y-m-d H:i:s') . ' ' . $message;
    if ($context) {
        $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    $logDir = dirname(__DIR__) . '/admin/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    @file_put_contents($logDir . '/site-errors.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function site_register_error_handlers(): void
{
    if (defined('SITE_ERROR_HANDLERS_REGISTERED')) {
        return;
    }
    define('SITE_ERROR_HANDLERS_REGISTERED', true);

    set_exception_handler(static function (Throwable $e): void {
        site_log_error('Uncaught exception: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if (site_is_debug()) {
            echo '<pre style="padding:2rem;color:#fff;background:#111;">'
                . htmlspecialchars($e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine())
                . '</pre>';
            exit;
        }

        $asset_base = $GLOBALS['asset_base'] ?? '/';
        $route_name = '500';
        include dirname(__DIR__) . '/includes/error-page.php';
        exit;
    });

    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}
