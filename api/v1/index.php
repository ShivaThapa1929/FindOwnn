<?php
/**
 * Findownn Mobile API - Entry Point
 * Version: 1.0.0
 * Base: /api/v1/
 */

// Define ROOT_PATH for admin dependencies
define('ROOT_PATH', __DIR__ . '/../../admin');

// Load admin .env for CORS config using Config class
require_once __DIR__ . '/../../admin/app/Core/Config.php';
\App\Core\Config::load(ROOT_PATH . '/.env');

$corsOrigins = \App\Core\Config::get('CORS_ALLOWED_ORIGINS', '');
$allowedOrigins = array_filter(array_map('trim', explode(',', $corsOrigins)));

// Disable HTML error display for JSON API
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Restrict CORS to allowed origins in non-dev environments
if (!empty($allowedOrigins)) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
} else {
    header('Access-Control-Allow-Origin: *');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Custom error handler to return JSON
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Check if required files exist
    $adminPath = __DIR__ . '/../../admin/app/Core/Database.php';
    if (!file_exists($adminPath)) {
        throw new Exception("Database class not found at: " . $adminPath);
    }
    
    require_once __DIR__ . '/../../admin/app/Core/Logger.php';
    require_once $adminPath;
    require_once __DIR__ . '/ApiController.php';

    if (!class_exists(\Api\V1\ApiController::class, false)) {
        throw new Exception(
            'ApiController failed to load. Re-upload api/v1/ApiController.php (must contain namespace Api\\V1).'
        );
    }

    // Parse request
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    // Remove /api/v1/ prefix and query string using substring search
    $path = parse_url($requestUri, PHP_URL_PATH);
    $apiPos = strpos($path, '/api/v1');
    if ($apiPos !== false) {
        $path = substr($path, $apiPos + strlen('/api/v1'));
    }
    $path = trim($path, '/');
    
    // Get query parameters
    parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);
    
    // Parse path segments
    $segments = $path ? explode('/', $path) : [];
    $resource = ($segments[0] ?? '') ?: ($queryParams['resource'] ?? '');
    $id = ($segments[1] ?? null) ?: ($queryParams['id'] ?? null);
    $action = ($segments[2] ?? null) ?: ($queryParams['action'] ?? null);
    
    // Get request body — JSON or form fallback
    $rawInput = file_get_contents('php://input');
    $requestBody = (!empty($rawInput)) ? (json_decode($rawInput, true) ?? []) : [];
    
    // Fallback to $_POST for form submissions
    if (empty($requestBody) && !empty($_POST)) {
        $requestBody = $_POST;
    }
    
    // Route to appropriate controller
    $response = \Api\V1\ApiController::route($resource, $requestMethod, $id, $action, $queryParams, $requestBody);
    
    // Send response (HTML for browser auth forms, JSON for API clients)
    http_response_code($response['status'] ?? 200);
    if (($response['content_type'] ?? '') === 'text/html') {
        header('Content-Type: text/html; charset=utf-8');
        echo $response['body'] ?? '';
    } else {
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'code' => 'SERVER_ERROR'
    ], JSON_PRETTY_PRINT);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error',
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'code' => 'FATAL_ERROR'
    ], JSON_PRETTY_PRINT);
}
