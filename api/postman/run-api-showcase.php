<?php
/**
 * CLI API showcase — run all key endpoints and print JSON results.
 * Usage: php api/postman/run-api-showcase.php
 * Requires Apache running at http://localhost/findownn_website
 */
declare(strict_types=1);

$base = getenv('FINDOWNN_BASE') ?: 'http://localhost/findownn_website';
$email = getenv('TEST_EMAIL') ?: 'testplayer@findownn.com';
$pass  = getenv('TEST_PASSWORD') ?: 'TestPlayer@123';

function api(string $base, string $method, string $path, ?array $body = null, ?string $token = null): array
{
    $url = rtrim($base, '/') . $path;
    $ch  = curl_init($url);
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        return ['http' => 0, 'error' => $err, 'body' => null];
    }
    $decoded = json_decode($raw, true);
    return ['http' => $code, 'body' => $decoded ?? $raw];
}

function section(string $title): void
{
    echo "\n" . str_repeat('=', 72) . "\n";
    echo "  {$title}\n";
    echo str_repeat('=', 72) . "\n";
}

function show(string $name, array $res): void
{
    $http = $res['http'] ?? 0;
    $ok   = $http >= 200 && $http < 300 ? 'OK' : 'FAIL';
    echo "\n--- {$name} [HTTP {$http} {$ok}] ---\n";
    if (!empty($res['error'])) {
        echo "CURL Error: {$res['error']}\n";
        return;
    }
    echo json_encode($res['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

// Connectivity check
$ping = api($base, 'GET', '/api/v1/');
if (($ping['http'] ?? 0) === 0) {
    echo "ERROR: Cannot reach {$base}/api/v1/\n";
    echo "Start XAMPP Apache first, then run:\n";
    echo "  php api/postman/run-api-showcase.php\n";
    exit(1);
}

section('01 — HEALTH');
show('GET /api/v1/', $ping);

section('02 — AUTH');
$login = api($base, 'POST', '/api/v1/auth/login', ['email' => $email, 'password' => $pass]);
show('POST /api/v1/auth/login', $login);

$token = $login['body']['data']['token'] ?? null;
if (!$token) {
    echo "\nWARNING: Login failed — skipping auth-required endpoints.\n";
    echo "Create test player: php create-test-player.php\n";
} else {
    echo "\n>> Token saved for next requests (first 20 chars): " . substr($token, 0, 20) . "...\n";
}

section('03 — SPORTS');
show('GET /api/v1/sports', api($base, 'GET', '/api/v1/sports'));

section('04 — VENUES');
$venues = api($base, 'GET', '/api/v1/venues?city=Bhuj&per_page=3');
show('GET /api/v1/venues?city=Bhuj', $venues);

$venueId = $venues['body']['data']['items'][0]['id'] ?? 1;
show('GET /api/v1/venues/' . $venueId, api($base, 'GET', "/api/v1/venues/{$venueId}"));
show('GET /api/v1/venues/' . $venueId . '/availability?date=2026-09-10',
    api($base, 'GET', "/api/v1/venues/{$venueId}/availability?date=2026-09-10"));

section('05 — COURTS');
$courts = api($base, 'GET', "/api/v1/courts?venue_id={$venueId}");
show("GET /api/v1/courts?venue_id={$venueId}", $courts);

$courtId = $courts['body']['data']['items'][0]['id'] ?? 1;
show('GET /api/v1/courts/' . $courtId, api($base, 'GET', "/api/v1/courts/{$courtId}"));
show('GET /api/v1/courts/' . $courtId . '/availability?date=2026-09-15',
    api($base, 'GET', "/api/v1/courts/{$courtId}/availability?date=2026-09-15"));

section('06 — SEARCH & CITIES');
show('GET /api/v1/search?q=box', api($base, 'GET', '/api/v1/search?q=box'));
show('GET /api/v1/cities', api($base, 'GET', '/api/v1/cities'));

if ($token) {
    section('07 — BOOKINGS (Auth)');
    show('GET /api/v1/bookings', api($base, 'GET', '/api/v1/bookings', null, $token));

    section('08 — USER (Auth)');
    show('GET /api/v1/user/profile', api($base, 'GET', '/api/v1/user/profile', null, $token));
    show('GET /api/v1/user/stats', api($base, 'GET', '/api/v1/user/stats', null, $token));
}

section('DONE');
echo "\nBase URL: {$base}\n";
echo "Import in Postman: api/postman/Findownn-API.postman_collection.json\n\n";
