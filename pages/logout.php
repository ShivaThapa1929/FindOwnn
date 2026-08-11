<?php
require_once __DIR__ . '/../includes/user-auth.php';

site_logout();
site_flash('success', 'You have been signed out.');
site_redirect('login');
