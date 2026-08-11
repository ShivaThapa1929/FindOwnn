<?php
/**
 * Venue Search API for Findownn
 * Searches: 1) Local hardcoded DB  2) Overpass API (OSM sports tags)  3) Nominatim (places)
 * All results filtered to Kutch district bounds
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/site-contact.php';
$contactEmail = $site_contact_email ?? 'findownn@gmail.com';

define('LAT_MIN', 22.7); define('LAT_MAX', 24.2);
define('LON_MIN', 68.5); define('LON_MAX', 71.2);

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($q) < 2) { echo json_encode([]); exit; }

// ============================================================
// 1. LOCAL HARDCODED DATABASE (Google Maps verified venues)
//    Add more venues here as you discover them
// ============================================================
$localVenues = [

    // ── BOX CRICKET TURFS (Bhuj) ──────────────────────────────────────
    ['name' => 'Pitch And Plates Cafe & Box Cricket, Bhuj',
        'lat' => 23.2535,  'lon' => 69.6553,  'type' => 'box_cricket',
        'area' => 'Vora Colony, Mundra-Bhuj Rd, near Chanakya Academy'],

    ['name' => 'Ashapura Box Cricket & Pickleball, Bhuj',
        'lat' => 23.2608,  'lon' => 69.6295,  'type' => 'box_cricket',
        'area' => 'Mundra-Bhuj Rd, near Nayra Petrol Pump, Silver City Township'],

    ['name' => 'Green Field Box Cricket & Football Turf, Bhuj',
        'lat' => 23.2498,  'lon' => 69.6628,  'type' => 'box_cricket',
        'area' => 'Central Bhuj'],

    ['name' => 'Paddle Pitch Box Cricket & Pickleball, Bhuj',
        'lat' => 23.2487,  'lon' => 69.6572,  'type' => 'box_cricket',
        'area' => 'Mundra Road, Bohra Colony, Bhuj'],

    ['name' => 'Hill Garden Box Cricket & Pickleball, Bhuj',
        'lat' => 23.2610,  'lon' => 69.6740,  'type' => 'box_cricket',
        'area' => 'Hill Garden Road, Uma Nagar, Bhuj'],

    ['name' => "The Prince's Arena Box Cricket, Bhuj",
        'lat' => 23.2315,  'lon' => 69.6930,  'type' => 'box_cricket',
        'area' => 'Plot 9/14, Madhapar-Bhujodi Highway, Bhuj'],

    ['name' => '7x Box Cricket, Bhuj',
        'lat' => 23.2520,  'lon' => 69.6690,  'type' => 'box_cricket',
        'area' => 'Station Road, Bhuj'],

    ['name' => 'Green Galaxy Box Cricket Ground, Bhuj',
        'lat' => 23.2465,  'lon' => 69.6610,  'type' => 'box_cricket',
        'area' => 'Din Dayal Nagar, Bhuj'],

    ['name' => 'Box Cricket Turf - Airport Ring Road, Bhuj',
        'lat' => 23.2589,  'lon' => 69.6364,  'type' => 'box_cricket',
        'area' => 'Airport Ring Road, Bhuj'],

    ['name' => 'Cricketeria Box Cricket, Bhuj',
        'lat' => 23.2510,  'lon' => 69.6640,  'type' => 'box_cricket',
        'area' => 'Bhuj'],

    ['name' => 'Kapil Cricket Ground, Kera',
        'lat' => 23.0871,  'lon' => 69.5957,  'type' => 'box_cricket',
        'area' => 'Kera, Kutch'],

    // ── PICKLEBALL COURTS (Bhuj/Kutch) ───────────────────────────────
    ['name' => '112 Arcadia - Pickleball (6 Courts), Bhuj',
        'lat' => 23.2133,  'lon' => 69.6542,  'type' => 'pickleball',
        'area' => 'Plot 4-9, Shree Valram Nagar-1, Opp KSKV Kachchh University, Bhuj'],

    ['name' => '112 Arcadia Central - Pickleball, Bhuj',
        'lat' => 23.2450,  'lon' => 69.6620,  'type' => 'pickleball',
        'area' => "Master's Tennis Academy, Lalan College Road, Jadavji Nagar, Bhuj"],

    ['name' => 'Ashapura Box Cricket & Pickleball, Bhuj',
        'lat' => 23.2608,  'lon' => 69.6295,  'type' => 'pickleball',
        'area' => 'Mundra-Bhuj Rd, near Nayra Petrol Pump, Silver City Township'],

    ['name' => 'Paddle Pitch Pickleball Court, Bhuj',
        'lat' => 23.2487,  'lon' => 69.6572,  'type' => 'pickleball',
        'area' => 'Mundra Road, Bohra Colony, Bhuj'],

    ['name' => 'Hill Garden Pickleball Court, Bhuj',
        'lat' => 23.2610,  'lon' => 69.6740,  'type' => 'pickleball',
        'area' => 'Hill Garden Road, Uma Nagar, Bhuj'],

    ['name' => 'Pitch And Paddle Sports Pickleball, Bhuj',
        'lat' => 23.2544,  'lon' => 69.6571,  'type' => 'pickleball',
        'area' => 'Bhuj'],

    // ── CRICKET GROUNDS ───────────────────────────────────────────────
    ['name' => 'Jubilee Cricket Ground, Bhuj',
        'lat' => 23.2505,  'lon' => 69.6710,  'type' => 'cricket',    'area' => 'Jubilee Circle, Bhuj'],
    ['name' => 'Anjar Cricket Stadium, Anjar',
        'lat' => 23.1047,  'lon' => 70.0338,  'type' => 'cricket',    'area' => 'Anjar, Kutch'],
    ['name' => 'Bhuj Cricket Ground (Main)',
        'lat' => 23.2420,  'lon' => 69.6669,  'type' => 'cricket',    'area' => 'Bhuj'],
    ['name' => 'Gandhidham Cricket Stadium',
        'lat' => 23.0753,  'lon' => 70.1337,  'type' => 'cricket',    'area' => 'Gandhidham, Kutch'],

    // ── FOOTBALL / FUTSAL TURFS ───────────────────────────────────────
    ['name' => 'Green Field Football Turf, Bhuj',
        'lat' => 23.2498,  'lon' => 69.6628,  'type' => 'football',   'area' => 'Bhuj'],
    ['name' => 'Bhuj Football Ground (Near Airport)',
        'lat' => 23.2588,  'lon' => 69.6350,  'type' => 'football',   'area' => 'Airport Road, Bhuj'],
    ['name' => 'Gandhidham Football Ground',
        'lat' => 23.0780,  'lon' => 70.1400,  'type' => 'football',   'area' => 'Gandhidham, Kutch'],

    // ── BADMINTON ─────────────────────────────────────────────────────
    ['name' => 'Bhuj Badminton Hall',
        'lat' => 23.2450,  'lon' => 69.6680,  'type' => 'badminton',  'area' => 'Bhuj'],

    // ── MULTI-SPORT COMPLEX ───────────────────────────────────────────
    ['name' => '112 Arcadia Sports Complex, Bhuj',
        'lat' => 23.2133,  'lon' => 69.6542,  'type' => 'sports_centre', 'area' => 'Opp KSKV University, Bhuj'],
    ['name' => 'Bhuj Sports Complex',
        'lat' => 23.2460,  'lon' => 69.6720,  'type' => 'sports_centre', 'area' => 'Bhuj'],
    ['name' => 'Mundra Sports Ground',
        'lat' => 22.8410,  'lon' => 69.7220,  'type' => 'sports_centre', 'area' => 'Mundra, Kutch'],
    ['name' => 'Gandhidham Sports Complex',
        'lat' => 23.0770,  'lon' => 70.1350,  'type' => 'sports_centre', 'area' => 'Gandhidham, Kutch'],
];

// Search local DB (case-insensitive, partial match on name OR type)
$qLower = strtolower($q);
$localResults = [];
foreach ($localVenues as $v) {
    $nameLower = strtolower($v['name']);
    $typeLower = strtolower($v['type']);
    if (strpos($nameLower, $qLower) !== false || strpos($typeLower, $qLower) !== false ||
        // alias matches
        ($qLower === 'turf'      && in_array($v['type'], ['box_cricket','football','sports_centre'])) ||
        ($qLower === 'cricket'   && strpos($v['type'], 'cricket') !== false) ||
        ($qLower === 'box'       && $v['type'] === 'box_cricket') ||
        ($qLower === 'pickle'    && $v['type'] === 'pickleball') ||
        ($qLower === 'pickleball'&& $v['type'] === 'pickleball') ||
        ($qLower === 'football'  && $v['type'] === 'football') ||
        ($qLower === 'badminton' && $v['type'] === 'badminton') ||
        ($qLower === 'sport'     && true) ||
        ($qLower === 'sports'    && true) ||
        ($qLower === 'ground'    && in_array($v['type'], ['cricket','football','sports_centre'])) ||
        ($qLower === 'pitch'     && in_array($v['type'], ['box_cricket','cricket','football'])) ||
        ($qLower === 'venue'     && true) ||
        ($qLower === 'court'     && in_array($v['type'], ['pickleball','badminton']))
    ) {
        $localResults[] = [
            'lat'          => (string)$v['lat'],
            'lon'          => (string)$v['lon'],
            'display_name' => $v['name'],
            'type'         => $v['type'],
            'source'       => 'local',
        ];
    }
}

// ============================================================
// 2. OVERPASS API — OSM tagged sports venues
// ============================================================
$cacheDir = __DIR__ . '/../cache/venue_search/';
if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0755, true); }
$cacheKey  = 'vsearch_' . md5($qLower);
$cacheFile = $cacheDir . $cacheKey . '.json';

$overpassResults = [];

// Use cached overpass result (1hr cache)
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
    $overpassResults = json_decode(file_get_contents($cacheFile), true) ?: [];
} else {
    // Build Overpass query - search by name AND sports tags
    $escaped = addslashes($q);
    $overpassQuery = <<<OPQ
[out:json][timeout:12];
(
  node["name"~"{$escaped}",i]["leisure"~"pitch|sports_centre|recreation_ground"](22.7,68.5,24.2,71.2);
  node["name"~"{$escaped}",i]["sport"](22.7,68.5,24.2,71.2);
  node["name"~"{$escaped}",i]["amenity"="recreation_ground"](22.7,68.5,24.2,71.2);
  way["name"~"{$escaped}",i]["leisure"~"pitch|sports_centre|recreation_ground"](22.7,68.5,24.2,71.2);
  way["name"~"{$escaped}",i]["sport"](22.7,68.5,24.2,71.2);
);
out center;
OPQ;

    $ch = curl_init('https://overpass-api.de/api/interpreter');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'data=' . urlencode($overpassQuery),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 14,
        CURLOPT_USERAGENT      => 'Findownn/1.0 (venue-search; ' . $contactEmail . ')',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp && $code === 200) {
        $data = json_decode($resp, true);
        foreach (($data['elements'] ?? []) as $el) {
            $elLat = $el['lat'] ?? ($el['center']['lat'] ?? null);
            $elLon = $el['lon'] ?? ($el['center']['lon'] ?? null);
            if (!$elLat || !$elLon) continue;

            $name = $el['tags']['name'] ?? '';
            if (empty($name)) continue;
            $sport = $el['tags']['sport'] ?? $el['tags']['leisure'] ?? 'sports_venue';
            $overpassResults[] = [
                'lat'          => (string)$elLat,
                'lon'          => (string)$elLon,
                'display_name' => $name . ', Kutch',
                'type'         => $sport,
                'source'       => 'osm',
            ];
        }
        file_put_contents($cacheFile, json_encode($overpassResults));
    }
}

// ============================================================
// 3. NOMINATIM — general place name search (neighbourhoods etc.)
// ============================================================
$nominatimResults = [];
$nomCache = $cacheDir . 'nom_' . md5($qLower) . '.json';

if (file_exists($nomCache) && (time() - filemtime($nomCache)) < 86400) {
    $nominatimResults = json_decode(file_get_contents($nomCache), true) ?: [];
} else {
    $nomUrl = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&addressdetails=1&q='
        . urlencode($q . ', Kutch, Gujarat, India');
    $ch2 = curl_init($nomUrl);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_USERAGENT      => 'Findownn/1.0 (place-search; ' . $contactEmail . ')',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp2 = curl_exec($ch2);
    $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    if ($resp2 && $code2 === 200) {
        $data2 = json_decode($resp2, true) ?? [];
        foreach ($data2 as $r) {
            $rLat = (float)$r['lat']; $rLon = (float)$r['lon'];
            if ($rLat < LAT_MIN || $rLat > LAT_MAX || $rLon < LON_MIN || $rLon > LON_MAX) continue;
            $parts = array_slice(explode(',', $r['display_name']), 0, 3);
            $nominatimResults[] = [
                'lat'          => $r['lat'],
                'lon'          => $r['lon'],
                'display_name' => implode(', ', array_map('trim', $parts)),
                'type'         => $r['type'] ?? 'place',
                'source'       => 'nominatim',
            ];
        }
        file_put_contents($nomCache, json_encode($nominatimResults));
    }
}

// ============================================================
// MERGE: Local DB first → Overpass → Nominatim (deduplicate)
// ============================================================
$merged = $localResults;
$seen   = [];
foreach ($localResults as $r) {
    $seen[round((float)$r['lat'], 2) . ',' . round((float)$r['lon'], 2)] = true;
}

foreach (array_merge($overpassResults, $nominatimResults) as $r) {
    $key = round((float)$r['lat'], 2) . ',' . round((float)$r['lon'], 2);
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $merged[] = $r;
    }
}

echo json_encode(array_slice($merged, 0, 10));
