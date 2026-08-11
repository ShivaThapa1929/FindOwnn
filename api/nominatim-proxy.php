<?php
/**
 * Geocoding Proxy for Findownn
 * Primary: Photon API (Komoot) - no rate limits, fast, free
 * Fallback: Nominatim with caching
 * Kutch district bounds enforced server-side
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/site-contact.php';
$contactEmail = $site_contact_email ?? 'findownn@gmail.com';

// Kutch district bounding box
define('LAT_MIN', 22.7);
define('LAT_MAX', 24.2);
define('LON_MIN', 68.5);
define('LON_MAX', 71.2);

$q    = isset($_GET['q'])   ? trim($_GET['q'])   : '';
$type = isset($_GET['type'])? trim($_GET['type']): 'search';
$lat  = isset($_GET['lat']) ? (float)$_GET['lat'] : 0;
$lon  = isset($_GET['lon']) ? (float)$_GET['lon'] : 0;

if (empty($q) && $type !== 'reverse') {
    echo json_encode([]); exit;
}

// ---- Cache setup ----
$cacheDir  = __DIR__ . '/../cache/geocode/';
if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0755, true); }
$cacheKey  = $type === 'reverse' ? "rev_{$lat}_{$lon}" : 'search_' . md5(strtolower($q));
$cacheFile = $cacheDir . $cacheKey . '.json';

// Return cached result (24hr cache)
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
    echo file_get_contents($cacheFile);
    exit;
}

// ---- Helper: curl fetch ----
function fetchUrl($url, $ua = 'Findownn/1.0') {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_USERAGENT      => $ua,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$resp, $code];
}

// ---- Filter results to Kutch ----
function inKutch($lat, $lon) {
    return $lat >= LAT_MIN && $lat <= LAT_MAX && $lon >= LON_MIN && $lon <= LON_MAX;
}

// ============================================================
// REVERSE GEOCODING
// ============================================================
if ($type === 'reverse') {
    // Use Nominatim for reverse (Photon reverse needs OSM ID)
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}";
    list($resp, $code) = fetchUrl($url, 'Findownn/1.0 (reverse-geocode; ' . $contactEmail . ')');

    if ($resp && $code === 200) {
        file_put_contents($cacheFile, $resp);
        echo $resp;
    } else {
        // Best-effort: return coords as display
        echo json_encode(['display_name' => "Lat: {$lat}, Lon: {$lon}"]);
    }
    exit;
}

// ============================================================
// FORWARD SEARCH — Primary: Photon API
// ============================================================
$photonUrl = 'https://photon.komoot.io/api/?q=' . urlencode($q)
    . '&limit=10&lang=en'
    . '&bbox=' . LON_MIN . ',' . LAT_MIN . ',' . LON_MAX . ',' . LAT_MAX;

list($resp, $code) = fetchUrl($photonUrl, 'Findownn/1.0 (venue-locator)');

if ($resp && $code === 200) {
    $data = json_decode($resp, true);
    if (!empty($data['features'])) {
        $results = [];
        foreach ($data['features'] as $f) {
            $fLon = $f['geometry']['coordinates'][0];
            $fLat = $f['geometry']['coordinates'][1];
            if (!inKutch($fLat, $fLon)) continue;

            $p = $f['properties'] ?? [];
            // Build a clean display name
            $parts = array_filter([
                $p['name']     ?? '',
                $p['street']   ?? '',
                $p['district'] ?? $p['county'] ?? '',
                $p['city']     ?? $p['state']  ?? '',
            ]);
            $displayName = implode(', ', array_unique(array_values($parts)));
            if (empty($displayName)) $displayName = "Lat: {$fLat}, Lon: {$fLon}";

            $results[] = [
                'lat'          => (string)$fLat,
                'lon'          => (string)$fLon,
                'display_name' => $displayName,
                'type'         => $p['osm_value'] ?? $p['type'] ?? 'place',
                'class'        => $p['osm_key']   ?? 'place',
            ];
        }

        if (!empty($results)) {
            $json = json_encode($results);
            file_put_contents($cacheFile, $json);
            echo $json;
            exit;
        }
    }
}

// ============================================================
// Fallback: Nominatim (with delay to respect rate limit)
// ============================================================
$nomUrl = 'https://nominatim.openstreetmap.org/search?format=json&limit=8&addressdetails=1&q=' . urlencode($q . ', Kutch, Gujarat, India');
list($resp2, $code2) = fetchUrl($nomUrl, 'Findownn/1.0 (venue-locator; ' . $contactEmail . ')');

if ($resp2 && $code2 === 200) {
    $results2 = json_decode($resp2, true) ?? [];
    $filtered = array_filter($results2, function($r) {
        return inKutch((float)$r['lat'], (float)$r['lon']);
    });
    $filtered = array_values($filtered);
    $json = json_encode($filtered);
    file_put_contents($cacheFile, $json);
    echo $json;
    exit;
}

// Nothing found
echo json_encode([]);
