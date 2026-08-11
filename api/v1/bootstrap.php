<?php
/**
 * API v1 bootstrap — guaranteed load order for Hostinger / Apache
 */
if (!defined('API_V1_PATH')) {
    define('API_V1_PATH', __DIR__);
}

if (!class_exists(\App\Core\Database::class, false)) {
    require_once API_V1_PATH . '/../../admin/app/Core/Database.php';
}

if (!class_exists(\Api\V1\ApiController::class, false)) {
    require_once API_V1_PATH . '/ApiController.php';
}

if (!class_exists(\Api\V1\ApiController::class, false)) {
    throw new RuntimeException('Api\\V1\\ApiController failed to load');
}

function api_v1_require(string $controllerFile): void
{
    $path = API_V1_PATH . '/' . ltrim($controllerFile, '/');
    if (!is_file($path)) {
        throw new RuntimeException("API controller not found: {$controllerFile}");
    }
    require_once $path;
}
