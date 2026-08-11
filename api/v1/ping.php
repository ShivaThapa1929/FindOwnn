<?php
/**
 * API health check — upload verification for Hostinger
 * URL: /api/v1/ping.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$result = [
    'success' => true,
    'message' => 'API files OK',
    'checks' => [],
];

$files = [
    'ApiController.php',
    'SportController.php',
    'VenueController.php',
    'index.php',
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $result['checks'][$file] = [
        'exists' => is_file($path),
        'size'   => is_file($path) ? filesize($path) : 0,
    ];
}

require_once __DIR__ . '/../../admin/app/Core/Database.php';
require_once __DIR__ . '/ApiController.php';

$result['checks']['ApiController_class'] = class_exists(\Api\V1\ApiController::class, false);
$result['checks']['namespace_line'] = null;

$apiSource = @file_get_contents(__DIR__ . '/ApiController.php');
if ($apiSource !== false) {
    $result['checks']['namespace_line'] = str_contains($apiSource, 'namespace Api\\V1;');
}

require_once __DIR__ . '/SportController.php';
$result['checks']['SportController_class'] = class_exists(\Api\V1\SportController::class, false);

echo json_encode($result, JSON_PRETTY_PRINT);
