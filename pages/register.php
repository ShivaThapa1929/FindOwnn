<?php
require_once __DIR__ . '/../includes/user-auth.php';

if (site_user()) {
    site_redirect('dashboard');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!site_verify_csrf()) {
        site_flash('error', 'Invalid form submission. Please try again.');
    } else {
        $result = site_auth_register($_POST);
        if ($result['ok']) {
            site_flash('success', 'Welcome, ' . site_user()['name'] . '! Your dashboard is ready.');
            site_redirect('dashboard');
        }
        site_flash('error', $result['error']);
    }
}

site_redirect_auth('register');
