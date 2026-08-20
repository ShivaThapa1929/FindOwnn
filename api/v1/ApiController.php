<?php
namespace Api\V1;

use App\Core\Database;

class ApiController
{
    protected static $db;
    protected static $user = null;
    
    public static function init()
    {
        self::$db = Database::getInstance();
    }
    
    /**
     * Main routing function
     */
    public static function route($resource, $method, $id, $action, $query, $body)
    {
        self::init();
        
        // Authenticate if needed
        self::authenticate($body);
        
        // Route to appropriate controller
        switch ($resource) {
            case '':
            case 'health':
            case 'index':
                return self::health();
                
            case 'auth':
                require_once __DIR__ . '/AuthController.php';
                $htmlMode = self::wantsHtmlResponse($method);
                return AuthController::handle($method, $id, $body, $htmlMode);
                
            case 'venues':
                require_once __DIR__ . '/VenueController.php';
                return VenueController::handle($method, $id, $action, $query, $body);
                
            case 'courts':
                require_once __DIR__ . '/CourtController.php';
                return CourtController::handle($method, $id, $action, $query, $body);
                
            case 'sports':
                require_once __DIR__ . '/SportController.php';
                return SportController::handle($method, $id, $query);
                
            case 'bookings':
                require_once __DIR__ . '/BookingController.php';
                return BookingController::handle($method, $id, $action, $query, $body);
                
            case 'user':
                require_once __DIR__ . '/UserController.php';
                return UserController::handle($method, $id, $query, $body);
                
            case 'payments':
                require_once __DIR__ . '/PaymentController.php';
                return PaymentController::handle($method, $id, $body);
                
            case 'reviews':
                require_once __DIR__ . '/ReviewController.php';
                return ReviewController::handle($method, $id, $query, $body);
                
            case 'search':
                require_once __DIR__ . '/SearchController.php';
                return SearchController::handle($query);
                
            case 'cities':
                require_once __DIR__ . '/LocationController.php';
                return LocationController::handleCities($query);

            case 'location':
                require_once __DIR__ . '/LocationController.php';
                return LocationController::handleCities($query);

            default:
                return self::error('Resource not found: ' . $resource, 404, 'RESOURCE_NOT_FOUND');
        }
    }
    
    /**
     * Authenticate user from Bearer token
     * Supports PHP built-in server, Apache, and Nginx
     */
    protected static function authenticate(array $body = [])
    {
        $token = self::extractBearerToken($body);

        if (!empty($token)) {
            self::$user = self::verifyToken($token);
        }
    }

    /**
     * Extract bearer/API token from headers or POST body fallback
     */
    protected static function extractBearerToken(array $body = []): string
    {
        $authHeader = '';

        // Method 1: getallheaders() — works on Apache
        if (function_exists('getallheaders')) {
            $allHeaders = getallheaders();
            foreach ($allHeaders as $key => $value) {
                $header = strtolower($key);
                if ($header === 'authorization') {
                    $authHeader = $value;
                    break;
                }
                if ($header === 'x-auth-token' && empty($authHeader)) {
                    return trim($value);
                }
            }
        }

        // Method 2: $_SERVER fallback — works on PHP built-in server & Nginx
        if (empty($authHeader)) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION']
                ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                ?? '';
        }

        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            return trim($_SERVER['HTTP_X_AUTH_TOKEN']);
        }

        // Method 3: JSON/form body fallback for clients that cannot send headers
        if (!empty($body['token'])) {
            return trim((string) $body['token']);
        }
        if (!empty($body['api_token'])) {
            return trim((string) $body['api_token']);
        }
        if (!empty($_POST['token'])) {
            return trim((string) $_POST['token']);
        }

        return '';
    }
    
    /**
      * Verify JWT token
      */
    protected static function verifyToken($token)
    {
        $user = self::$db->fetch(
            "SELECT * FROM users WHERE api_token = ? AND deleted_at IS NULL",
            [$token]
        );
        
        if ($user && !empty($user['api_token_expires_at']) && strtotime($user['api_token_expires_at']) < time()) {
            return null;
        }
        
        return $user ?: null;
    }
    
    /**
     * Check if user is authenticated and email verified
     */
    protected static function requireAuth()
    {
        if (!self::$user) {
            self::sendResponse(self::error('Authentication required', 401, 'AUTH_REQUIRED'));
        }

        if (self::$user['role'] === 'venue_owner' && (empty(self::$user['email_verified_at']) || self::$user['status'] === 'pending_email_verification')) {
            self::sendResponse(self::error('Email verification required. Please verify your email address before accessing your dashboard.', 403, 'EMAIL_VERIFICATION_REQUIRED'));
        }
    }

    /**
     * Site root URL (works on Hostinger root and local subfolder installs)
     */
    protected static function getSiteBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

        if (strpos($script, '/api/v1') !== false) {
            $basePath = substr($script, 0, strpos($script, '/api/v1'));
        } elseif (strpos($script, '/admin') !== false) {
            $basePath = substr($script, 0, strpos($script, '/admin'));
        } else {
            $basePath = dirname($script);
            if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
                $basePath = '';
            }
        }

        return rtrim($protocol . '://' . $host . rtrim($basePath, '/'), '/');
    }

    /**
     * Build absolute image URL for uploads and static assets
     */
    public static function formatImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }

        $base = self::getSiteBaseUrl();

        if (strpos($path, 'assets/') === 0) {
            return $base . '/' . ltrim($path, '/');
        }

        if (strpos($path, 'public/uploads/') === 0) {
            return $base . '/admin/' . ltrim($path, '/');
        }

        return $base . '/admin/public/uploads/' . ltrim($path, '/');
    }

    /**
     * SQL: featured image from venue_images, else first gallery image, else venues column
     */
    protected static function venueFeaturedImageSql(string $venueAlias = 'v'): string
    {
        return "COALESCE(
            (SELECT image_path FROM venue_images WHERE venue_id = {$venueAlias}.id AND image_type = 'featured' ORDER BY sort_order ASC, id ASC LIMIT 1),
            (SELECT image_path FROM venue_images WHERE venue_id = {$venueAlias}.id ORDER BY sort_order ASC, id ASC LIMIT 1),
            {$venueAlias}.featured_image
        )";
    }

    /**
     * SQL: featured image from court_images, else first gallery image, else courts column
     */
    protected static function courtFeaturedImageSql(string $courtAlias = 'c'): string
    {
        return "COALESCE(
            (SELECT image_path FROM court_images WHERE court_id = {$courtAlias}.id AND image_type = 'featured' ORDER BY sort_order ASC, id ASC LIMIT 1),
            (SELECT image_path FROM court_images WHERE court_id = {$courtAlias}.id ORDER BY sort_order ASC, id ASC LIMIT 1),
            {$courtAlias}.featured_image
        )";
    }

    /**
     * Browser form (GET or form POST) vs JSON API client (app/Postman)
     */
    public static function wantsHtmlResponse(string $method): bool
    {
        if ($method === 'GET') {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        // Flutter / Postman JSON requests
        if (stripos($contentType, 'application/json') !== false) {
            return false;
        }
        if (stripos($accept, 'application/json') !== false && stripos($accept, 'text/html') === false) {
            return false;
        }

        // HTML form submit
        if (!empty($_POST)) {
            return true;
        }

        return stripos($accept, 'text/html') !== false;
    }

    /**
     * GET /api/v1/ — API health & available routes
     */
    protected static function health()
    {
        return self::success([
            'name' => 'FindOwnn Mobile API',
            'version' => '1.0.0',
            'status' => 'online',
            'endpoints' => [
                'GET  /api/v1/sports',
                'GET  /api/v1/venues?city=Bhuj',
                'GET  /api/v1/venues/{id}',
                'GET  /api/v1/venues/{id}/images',
                'GET  /api/v1/venues/{id}/availability?date=YYYY-MM-DD',
                'GET  /api/v1/courts?venue_id={id}',
                'GET  /api/v1/courts/{id}/availability?date=YYYY-MM-DD',
                'POST /api/v1/auth/login   (browser: open URL for form)',
                'POST /api/v1/auth/register (browser: open URL for form)',
                'POST /api/v1/auth/logout  (browser: open URL for form)',
                'POST /api/v1/bookings',
                'POST /api/v1/payments/initiate',
                'POST /api/v1/payments/verify',
            ],
        ], 'FindOwnn API is running');
    }

    protected static function success($data, $message = 'Success', $status = 200)
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => date('c'),
                'version' => '1.0.0'
            ],
            'status' => $status
        ];
    }
    
    /**
     * Error response
     */
    protected static function error($message, $status = 400, $code = 'ERROR')
    {
        return [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'status' => $status
        ];
    }
    
    /**
     * Paginate results
     */
    protected static function paginate($query, $params, $page = 1, $perPage = 20)
    {
        $perPage = min($perPage, 50); // Max 50 items per page
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countQuery = preg_replace('/SELECT .* FROM/i', 'SELECT COUNT(*) as total FROM', $query);
        $total = (int) self::$db->fetchColumn($countQuery, $params);
        
        // Get paginated results
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $items = self::$db->fetchAll($query, $params);
        
        return [
            'items' => $items,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_more' => $page < ceil($total / $perPage),
                'next_page' => $page < ceil($total / $perPage) ? $page + 1 : null,
                'prev_page' => $page > 1 ? $page - 1 : null
            ]
        ];
    }
    
    /**
     * Send response and exit
     */
    protected static function sendResponse($response)
    {
        http_response_code($response['status'] ?? 200);
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}
