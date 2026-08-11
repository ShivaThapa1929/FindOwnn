<?php
/**
 * Partner Request Form API Handler
 * Handles partner registration submissions with database validation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

define('ROOT_PATH', __DIR__ . '/../admin');

try {
    require_once __DIR__ . '/../admin/app/Core/Config.php';
    require_once __DIR__ . '/../admin/app/Core/Logger.php';
    require_once __DIR__ . '/../admin/app/Core/Database.php';

    $db = \App\Core\Database::getInstance();

    // Get input data (JSON or POST)
    $rawInput = file_get_contents('php://input');
    $input = !empty($rawInput) ? (json_decode($rawInput, true) ?? []) : [];

    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }

    $ownerName  = trim($input['owner_name'] ?? '');
    $phone      = trim($input['phone'] ?? '');
    $venueName  = trim($input['venue_name'] ?? '');
    $state      = trim($input['state'] ?? 'Gujarat');
    $city       = trim($input['city'] ?? 'Bhuj');
    $area       = trim($input['area'] ?? '');
    $latitude   = !empty($input['latitude']) ? floatval($input['latitude']) : null;
    $longitude  = !empty($input['longitude']) ? floatval($input['longitude']) : null;
    $mapAddress = trim($input['map_address'] ?? '');
    $sportsArr  = $input['sports'] ?? [];
    $comments   = trim($input['comments'] ?? '');

    if (is_string($sportsArr)) {
        $sportsArr = array_filter(array_map('trim', explode(',', $sportsArr)));
    }

    $errors = [];

    // 1. Owner Name Validation
    if (empty($ownerName)) {
        $errors['owner_name'] = 'Full Name is required.';
    } elseif (strlen($ownerName) < 3) {
        $errors['owner_name'] = 'Full Name must be at least 3 characters.';
    } elseif (!preg_match("/^[a-zA-Z\s\.\'-]+$/", $ownerName)) {
        $errors['owner_name'] = 'Full Name must contain only alphabets and spaces.';
    }

    // 2. Phone Number Validation (10-digit Indian Mobile)
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (strlen($cleanPhone) !== 10 || !preg_match('/^[6-9]\d{9}$/', $cleanPhone)) {
        $errors['phone'] = 'Please enter a valid 10-digit Indian phone number starting with 6, 7, 8, or 9.';
    }

    // 3. Playground Name Validation
    if (empty($venueName)) {
        $errors['venue_name'] = 'Playground name is required.';
    } elseif (strlen($venueName) < 3) {
        $errors['venue_name'] = 'Playground name must be at least 3 characters.';
    }

    // 4. State Validation (Strict: Default & Only Gujarat allowed)
    if (empty($state) || strtolower($state) !== 'gujarat') {
        $errors['state'] = 'Currently playground onboarding is available exclusively in Gujarat.';
    }

    // 5. City Validation (Default: Bhuj)
    if (empty($city)) {
        $errors['city'] = 'City is required.';
    }

    // 6. Area / Address Validation
    if (empty($area)) {
        $errors['area'] = 'Playground area / location details are required.';
    }

    // 7. Sports Validation (At least 1 required)
    if (empty($sportsArr)) {
        $errors['sports'] = 'Please select at least one sports facility available at your playground.';
    }

    // Return errors if any
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed. Please correct the highlighted errors.',
            'errors'  => $errors
        ]);
        exit;
    }

    // Insert into Database
    $sportsString = implode(', ', $sportsArr);

    $requestId = $db->insert(
        "INSERT INTO partner_requests 
         (owner_name, phone, venue_name, state, city, area, latitude, longitude, map_address, sports, comments, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
        [
            $ownerName,
            $cleanPhone,
            $venueName,
            'Gujarat',
            $city,
            $area,
            $latitude,
            $longitude,
            $mapAddress,
            $sportsString,
            $comments
        ]
    );

    http_response_code(201);
    echo json_encode([
        'success'    => true,
        'message'    => 'Playground listing submitted successfully! Our verification team will visit and contact you within 24 hours.',
        'request_id' => $requestId
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error while processing request: ' . $e->getMessage()
    ]);
}
