<?php

/**
 * Findownn Admin — Application Entry Point
 * All requests are routed through this file.
 */

declare(strict_types=1);

// ----------------------------------------------------------------
// 1. Define root path
// ----------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__));

// ----------------------------------------------------------------
// 2. Autoloader (Composer PSR-4 or manual fallback)
// ----------------------------------------------------------------
$composerAutoload = ROOT_PATH . '/vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require $composerAutoload;
} else {
    // Manual PSR-4 autoloader fallback (when Composer isn't run yet)
    spl_autoload_register(function (string $class): void {
        $prefixes = [
            'App\\'      => ROOT_PATH . '/app/',
            'Database\\' => ROOT_PATH . '/database/',
        ];

        foreach ($prefixes as $prefix => $base) {
            if (str_starts_with($class, $prefix)) {
                $relative = substr($class, strlen($prefix));
                $file     = $base . str_replace('\\', '/', $relative) . '.php';
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        }
    });

    // Load helper functions manually
    require ROOT_PATH . '/app/Helpers/functions.php';
}

// ----------------------------------------------------------------
// 3. Bootstrap core services
// ----------------------------------------------------------------
use App\Core\Config;
use App\Core\Session;
use App\Core\Logger;
use App\Core\Router;
use App\Core\Request;

Config::load(ROOT_PATH . '/.env');

// Timezone
date_default_timezone_set(Config::get('TIMEZONE', 'Asia/Kolkata'));

// Error handling
if (Config::get('APP_DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

Logger::init();
Session::start();

// Favicon for /admin/* pages (browser default request)
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
if (preg_match('#/favicon\.ico$#i', $reqPath)) {
    $icon = __DIR__ . '/assets/images/favicon-32x32.png';
    if (is_file($icon)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800');
        readfile($icon);
        exit;
    }
}

// ----------------------------------------------------------------
// 4. Security headers
// ----------------------------------------------------------------
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Booking/dashboard pages must never be served from browser cache
if (preg_match('#/(bookings|dashboard)(/|$)#', $reqPath)) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// ----------------------------------------------------------------
// 5. Auto-expire subscriptions (runs 5% of requests)
// ----------------------------------------------------------------
if (mt_rand(1, 20) === 1) {
    try {
        (new \App\Models\Subscription())->expireOld();
    } catch (\Throwable) {}
}

// ----------------------------------------------------------------
// 6. Load routes and dispatch
// ----------------------------------------------------------------
$router  = new Router();
$request = new Request();

require ROOT_PATH . '/routes/web.php';

// Debug helper: visit /findownn_website/admin/?debug=1 to see routing info
if (Config::get('APP_DEBUG') === 'true' && isset($_GET['debug'])) {
    header('Content-Type: text/plain');
    echo "REQUEST_URI:    " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
    echo "SCRIPT_NAME:    " . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
    echo "PHP_SELF:       " . ($_SERVER['PHP_SELF'] ?? '') . "\n";
    echo "APP_URL:        " . Config::get('APP_URL') . "\n";
    
    // Reflect into Router to print details
    $refObject = new ReflectionObject($router);
    $refMethod = $refObject->getMethod('normalizeUri');
    $refMethod->setAccessible(true);
    $normalized = $refMethod->invoke($router, $request->getUri());
    echo "NORMALIZED URI: " . $normalized . "\n";
    echo "METHOD:         " . $request->getMethod() . "\n\n";
    
    $refRoutes = $refObject->getProperty('routes');
    $refRoutes->setAccessible(true);
    $routes = $refRoutes->getValue($router);
    echo "REGISTERED ROUTES:\n";
    foreach ($routes as $route) {
        echo "  [{$route['method']}] {$route['fullPath']} -> " . 
             (is_array($route['handler']) ? implode('@', $route['handler']) : 'Closure') . "\n";
    }
    exit;
}

try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    Logger::error('Unhandled exception: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);

    if (Config::get('APP_DEBUG') === 'true') {
        echo '<pre style="background:#1a1a2e;color:#e2e8f0;padding:20px;margin:20px;border-radius:8px;">';
        echo '<strong style="color:#f56565;">' . get_class($e) . '</strong>: ' . htmlspecialchars($e->getMessage());
        echo "\nFile: " . $e->getFile() . ':' . $e->getLine();
        echo "\n\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>500 — Something went wrong.</h1>';
    }
}
