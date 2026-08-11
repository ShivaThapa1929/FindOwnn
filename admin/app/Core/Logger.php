<?php

namespace App\Core;

/**
 * Logger — Simple PSR-3 inspired file logger.
 */
class Logger
{
    private static string $logDir = '';

    public static function init(): void
    {
        self::$logDir = ROOT_PATH . '/storage/logs';
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (Config::get('APP_DEBUG') !== 'true') return;
        self::write('DEBUG', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        if (self::$logDir === '') self::init();

        $file = self::$logDir . '/' . date('Y-m-d') . '.log';
        $ctx  = empty($context) ? '' : ' ' . json_encode($context);
        $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $message . $ctx . PHP_EOL;

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
