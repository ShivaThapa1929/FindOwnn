<?php
/**
 * AJAX auth endpoints for site auth modal (JSON responses)
 */

require_once __DIR__ . '/site-errors.php';
require_once __DIR__ . '/user-auth.php';

function auth_json(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function auth_handle_login(): void
{
    if (!site_verify_csrf()) {
        auth_json(['ok' => false, 'error' => 'Session expired. Please refresh and try again.'], 403);
    }

    try {
        $loginAs = trim($_POST['login_as'] ?? 'player');
        $result  = site_auth_login($_POST['email'] ?? '', $_POST['password'] ?? '', $loginAs);
    } catch (\Throwable $e) {
        site_log_error('Login error: ' . $e->getMessage());
        auth_json(['ok' => false, 'error' => 'We\'re unavailable right now. Please try again in a few minutes.'], 503);
    }

    if ($result['ok']) {
        site_flash('success', 'Welcome back, ' . site_user()['name'] . '!');
        auth_json([
            'ok'          => true,
            'message'     => 'Welcome back!',
            'redirect'    => $result['redirect'] ?? 'dashboard',
            'user'        => site_user(),
        ]);
    }

    auth_json([
        'ok'           => false,
        'error'        => $result['error'],
        'admin'        => !empty($result['admin']) || !empty($result['portal']),
        'portal'       => $result['portal'] ?? null,
        'redirect_url' => $result['redirect_url'] ?? null,
    ], 401);
}

function auth_handle_register(): void
{
    if (!site_verify_csrf()) {
        auth_json(['ok' => false, 'error' => 'Session expired. Please refresh and try again.'], 403);
    }

    try {
        $result = site_auth_register($_POST);
    } catch (\Throwable $e) {
        site_log_error('Register error: ' . $e->getMessage());
        auth_json(['ok' => false, 'error' => 'We\'re unavailable right now. Please try again in a few minutes.'], 503);
    }

    if ($result['ok']) {
        site_flash('success', 'Welcome, ' . site_user()['name'] . '! Your dashboard is ready.');
        auth_json([
            'ok'       => true,
            'message'  => 'Account created successfully!',
            'redirect' => 'dashboard',
            'user'     => site_user(),
        ], 201);
    }

    auth_json(['ok' => false, 'error' => $result['error']], 422);
}
