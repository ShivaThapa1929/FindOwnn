<?php
/**
 * Simple API Test - No Database Required
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => 'API is working!',
    'version' => '1.0.0',
    'timestamp' => date('Y-m-d H:i:s'),
    'endpoints' => [
        '/api/v1/test.php' => 'This test endpoint',
        '/api/v1/venues' => 'Get all venues',
        '/api/v1/sports' => 'Get all sports',
        '/api/v1/search' => 'Search venues'
    ]
], JSON_PRETTY_PRINT);
