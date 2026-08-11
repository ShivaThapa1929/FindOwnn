<?php
/**
 * Findownn Website - Front Controller Router
 * Dynamically routes requests to corresponding view files.
 */

// Session — configured in includes/user-auth.php when needed

$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($request_uri, PHP_URL_PATH);

// Dynamically compute base path from SCRIPT_NAME
// e.g. /findownn_website/index.php  → base is /findownn_website
// e.g. /index.php (root deploy)     → base is ''
$script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$asset_base = ($script_dir === '') ? '/' : $script_dir . '/';

if ($script_dir !== '' && strpos($path, $script_dir) === 0) {
    $path = substr($path, strlen($script_dir));
}

// Strip trailing slashes and clean up
$path = '/' . trim($path, '/');

// Favicon — browsers often request /favicon.ico before parsing HTML
if ($path === '/favicon.ico') {
    $icon = __DIR__ . '/assets/images/favicon-32x32.png';
    if (is_file($icon)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800');
        readfile($icon);
        exit;
    }
}

// Intercept admin requests and dispatch to admin application
if ($path === '/admin' || strpos($path, '/admin/') === 0) {
    require_once __DIR__ . '/admin/public/index.php';
    exit;
}

// API v1 — always dispatch to dedicated entry (avoids root router conflicts)
if ($path === '/api/v1' || strpos($path, '/api/v1/') === 0) {
    require_once __DIR__ . '/api/v1/index.php';
    exit;
}

// Site auth AJAX (modal login/register)
if ($path === '/auth/login' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/includes/auth-handler.php';
    auth_handle_login();
}
if ($path === '/auth/register' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/includes/auth-handler.php';
    auth_handle_register();
}

// Mapping routes to views and route names
$routes = [
    '/' => [
        'file' => 'pages/home.php',
        'name' => 'index'
    ],
    '/home' => [
        'file' => 'pages/home.php',
        'name' => 'index'
    ],
    '/index.php' => [
        'file' => 'pages/home.php',
        'name' => 'index'
    ],
    '/about' => [
        'file' => 'pages/about.php',
        'name' => 'about'
    ],
    '/about.php' => [
        'file' => 'pages/about.php',
        'name' => 'about'
    ],
    '/contact' => [
        'file' => 'pages/contact.php',
        'name' => 'contact'
    ],
    '/contact.php' => [
        'file' => 'pages/contact.php',
        'name' => 'contact'
    ],
    '/partner' => [
        'file' => 'pages/partner.php',
        'name' => 'partner'
    ],
    '/partner.php' => [
        'file' => 'pages/partner.php',
        'name' => 'partner'
    ],
    '/sports' => [
        'file' => 'pages/sports.php',
        'name' => 'sports'
    ],
    '/sports.php' => [
        'file' => 'pages/sports.php',
        'name' => 'sports'
    ],
    '/venues' => [
        'file' => 'pages/venues.php',
        'name' => 'venues'
    ],
    '/venues.php' => [
        'file' => 'pages/venues.php',
        'name' => 'venues'
    ],
    '/venue-details' => [
        'file' => 'pages/venue-details.php',
        'name' => 'venue-details'
    ],
    '/venue-details.php' => [
        'file' => 'pages/venue-details.php',
        'name' => 'venue-details'
    ],
    '/booking-payment' => [
        'file' => 'pages/booking-payment.php',
        'name' => 'booking-payment'
    ],
    '/booking-payment.php' => [
        'file' => 'pages/booking-payment.php',
        'name' => 'booking-payment'
    ],
    '/login' => [
        'file' => 'pages/login.php',
        'name' => 'login'
    ],
    '/login.php' => [
        'file' => 'pages/login.php',
        'name' => 'login'
    ],
    '/register' => [
        'file' => 'pages/register.php',
        'name' => 'register'
    ],
    '/register.php' => [
        'file' => 'pages/register.php',
        'name' => 'register'
    ],
    '/account' => [
        'file' => 'pages/account.php',
        'name' => 'account'
    ],
    '/account.php' => [
        'file' => 'pages/account.php',
        'name' => 'account'
    ],
    '/dashboard' => [
        'file' => 'pages/dashboard.php',
        'name' => 'dashboard'
    ],
    '/dashboard.php' => [
        'file' => 'pages/dashboard.php',
        'name' => 'dashboard'
    ],
    '/logout' => [
        'file' => 'pages/logout.php',
        'name' => 'logout'
    ],
    '/logout.php' => [
        'file' => 'pages/logout.php',
        'name' => 'logout'
    ]
];

// If path corresponds to a route, load it
if (isset($routes[$path])) {
    $route = $routes[$path];
    $route_name = $route['name'];
    require_once __DIR__ . '/' . $route['file'];
    exit;
}

// Fallback: If requesting a clean path that matches a file in pages directory, load it
$clean_path = trim($path, '/');
if (!empty($clean_path) && file_exists(__DIR__ . '/pages/' . $clean_path . '.php')) {
    $route_name = $clean_path;
    require_once __DIR__ . '/pages/' . $clean_path . '.php';
    exit;
}

// 404 Page Not Found
http_response_code(404);
$route_name = '404';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5 text-center min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="margin-top:75px;">
    <div class="glow-orb glow-orb-bottom-left"></div>
    <div class="container position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">Error 404</span>
        <h1 class="display-1 fw-bold text-white mb-2">Page Not Found</h1>
        <p class="text-secondary lead mx-auto mb-5" style="max-width: 500px;">
            The page you are looking for does not exist or has been moved to a new address.
        </p>
        <a href="./" class="btn btn-premium btn-shimmer">
            <i class="bi bi-house-door me-2"></i><span>Back to Home</span>
        </a>
    </div>
</section>
<?php
include __DIR__ . '/includes/footer.php';
?>
