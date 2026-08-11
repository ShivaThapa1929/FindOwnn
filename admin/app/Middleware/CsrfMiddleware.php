<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

/** CsrfMiddleware — Validates CSRF token on all POST/PUT/DELETE requests */
class CsrfMiddleware
{
    private array $except = ['/login', '/api'];

    public function handle(Request $request, callable $next): void
    {
        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $uri = parse_url($request->getUri(), PHP_URL_PATH);

            foreach ($this->except as $path) {
                if (str_starts_with($uri, $path)) {
                    $next();
                    return;
                }
            }

            $token = $_POST['_csrf'] ?? $request->header('X-CSRF-Token') ?? '';

            if (!Session::verifyCsrf($token)) {
                if ($request->wantsJson() || $request->isAjax() || str_contains($request->header('Accept') ?? '', 'application/json')) {
                    http_response_code(419);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'CSRF token mismatch or session expired. Refresh the page and try again.']);
                    exit;
                }
                Session::flash('error', 'Session expired. Please refresh the page and try again.');
                $referer = $_SERVER['HTTP_REFERER'] ?? '';
                if ($referer !== '') {
                    redirect($referer);
                }
                redirect(url('/owner/login'));
            }
        }

        $next();
    }
}
