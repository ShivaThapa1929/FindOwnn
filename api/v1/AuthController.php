<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class AuthController extends ApiController
{
    public static function handle($method, $action, $body, $htmlMode = false)
    {
        switch ($action) {
            case 'register':
                if ($method === 'GET') {
                    return self::registerForm();
                }
                if ($method === 'POST') {
                    $result = self::register($body);
                    return $htmlMode ? self::htmlFromResult($result, 'register', $body) : $result;
                }
                return self::error('Method not allowed', 405);

            case 'login':
                if ($method === 'GET') {
                    return self::loginForm();
                }
                if ($method === 'POST') {
                    $result = self::login($body);
                    return $htmlMode ? self::htmlFromResult($result, 'login', $body) : $result;
                }
                return self::error('Method not allowed', 405);

            case 'logout':
                if ($method === 'GET') {
                    $token = $_GET['token'] ?? '';
                    return self::logoutForm($token);
                }
                if ($method === 'POST') {
                    $result = self::logout($body);
                    return $htmlMode ? self::htmlFromResult($result, 'logout', $body) : $result;
                }
                return self::error('Method not allowed', 405);

            case 'refresh':
                if ($method === 'GET') {
                    return self::refreshForm($_GET['token'] ?? '');
                }
                if ($method === 'POST') {
                    $result = self::refresh();
                    return $htmlMode ? self::htmlFromResult($result, 'refresh', $body) : $result;
                }
                return self::error('Method not allowed', 405);

            default:
                return self::error('Invalid action', 404);
        }
    }

    private static function loginForm(array $values = [], ?string $error = null): array
    {
        $email = htmlspecialchars($values['email'] ?? '', ENT_QUOTES, 'UTF-8');

        return self::htmlPage('Login', '
            <h1>Login</h1>
            <p class="sub">Sign in to FindOwnn — browser test for mobile API</p>
            ' . self::alert($error) . '
            <form method="post" action="">
                <label>Email</label>
                <input type="email" name="email" value="' . $email . '" required placeholder="you@email.com">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Your password">
                <button type="submit">Login</button>
            </form>
            <div class="links">
                <a href="register">Create account</a>
                <a href="logout">Logout</a>
                <a href="../">API home</a>
            </div>
        ');
    }

    private static function registerForm(array $values = [], ?string $error = null, array $fieldErrors = []): array
    {
        $name = htmlspecialchars($values['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($values['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($values['phone'] ?? '', ENT_QUOTES, 'UTF-8');

        $fieldMsg = '';
        foreach ($fieldErrors as $field => $messages) {
            $msg = is_array($messages) ? implode(', ', $messages) : (string) $messages;
            $fieldMsg .= '<div class="alert">' . htmlspecialchars(ucfirst($field) . ': ' . $msg, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        return self::htmlPage('Register', '
            <h1>Register</h1>
            <p class="sub">Create a player account via API</p>
            ' . self::alert($error) . $fieldMsg . '
            <form method="post" action="">
                <label>Full name</label>
                <input type="text" name="name" value="' . $name . '" required>
                <label>Email</label>
                <input type="email" name="email" value="' . $email . '" required>
                <label>Phone</label>
                <input type="tel" name="phone" value="' . $phone . '" required placeholder="10-digit mobile">
                <label>Password</label>
                <input type="password" name="password" required minlength="8" placeholder="Min 8 characters">
                <label>Confirm password</label>
                <input type="password" name="password_confirmation" required minlength="8">
                <button type="submit">Register</button>
            </form>
            <div class="links">
                <a href="login">Already have account?</a>
                <a href="../">API home</a>
            </div>
        ');
    }

    private static function logoutForm(string $token = ''): array
    {
        $tokenSafe = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

        return self::htmlPage('Logout', '
            <h1>Logout</h1>
            <p class="sub">Invalidate your API token</p>
            <form method="post" action="">
                <label>API Token</label>
                <textarea name="token" rows="3" required placeholder="Paste token from login response">' . $tokenSafe . '</textarea>
                <button type="submit" class="btn-danger">Logout</button>
            </form>
            <div class="links">
                <a href="login">Login</a>
                <a href="register">Register</a>
                <a href="../">API home</a>
            </div>
        ');
    }

    private static function refreshForm(string $token = ''): array
    {
        $tokenSafe = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

        return self::htmlPage('Refresh Token', '
            <h1>Refresh token</h1>
            <p class="sub">Get a new API token</p>
            <form method="post" action="">
                <label>Current API Token</label>
                <textarea name="token" rows="3" required placeholder="Paste current token">' . $tokenSafe . '</textarea>
                <button type="submit">Refresh token</button>
            </form>
            <div class="links">
                <a href="login">Login</a>
                <a href="logout">Logout</a>
            </div>
        ');
    }

    private static function htmlFromResult(array $result, string $action, array $body): array
    {
        if (!empty($result['success'])) {
            return self::htmlSuccessPage($result, $action);
        }

        $message = $result['message'] ?? 'Request failed';
        $errors = $result['errors'] ?? [];

        if ($action === 'register') {
            return self::registerForm($body, $message, $errors);
        }
        if ($action === 'login') {
            return self::loginForm($body, $message);
        }
        if ($action === 'logout') {
            $form = self::logoutForm($body['token'] ?? '');
            $form['body'] = str_replace(
                '<form method="post"',
                self::alert($message) . '<form method="post"',
                $form['body']
            );
            return $form;
        }

        return self::htmlPage('Error', '
            <h1>Something went wrong</h1>
            ' . self::alert($message) . '
            <pre class="json">' . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . '</pre>
            <div class="links"><a href="login">Back to login</a></div>
        ', $result['status'] ?? 400);
    }

    private static function htmlSuccessPage(array $result, string $action): array
    {
        $data = $result['data'] ?? [];
        $token = htmlspecialchars($data['token'] ?? '', ENT_QUOTES, 'UTF-8');
        $json = htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

        $userBlock = '';
        if (!empty($data['user']) && is_array($data['user'])) {
            $user = $data['user'];
            $userBlock = '
                <div class="card">
                    <strong>' . htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') . '</strong><br>
                    <span class="muted">' . htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>
                </div>';
        }

        $tokenBlock = '';
        if ($token !== '') {
            $logoutUrl = 'logout?token=' . urlencode($data['token'] ?? '');
            $refreshUrl = 'refresh?token=' . urlencode($data['token'] ?? '');
            $tokenBlock = '
                <label>Your API token (use in app / Authorization header)</label>
                <textarea class="token" readonly onclick="this.select()">' . $token . '</textarea>
                <p class="hint">Header: <code>Authorization: Bearer ' . $token . '</code></p>
                <div class="links">
                    <a href="' . $logoutUrl . '">Logout with this token</a>
                    <a href="' . $refreshUrl . '">Refresh token</a>
                </div>';
        }

        $titles = [
            'login' => 'Login successful',
            'register' => 'Registration successful',
            'logout' => 'Logged out',
            'refresh' => 'Token refreshed',
        ];

        return self::htmlPage($titles[$action] ?? 'Success', '
            <h1>' . ($titles[$action] ?? 'Success') . '</h1>
            <p class="sub">' . htmlspecialchars($result['message'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>
            ' . $userBlock . $tokenBlock . '
            <details class="json-wrap">
                <summary>Raw JSON response</summary>
                <pre class="json">' . $json . '</pre>
            </details>
            <div class="links">
                <a href="login">Login</a>
                <a href="register">Register</a>
                <a href="../venues?city=Bhuj">Browse venues (JSON)</a>
            </div>
        ', $result['status'] ?? 200);
    }

    private static function alert(?string $message): string
    {
        if ($message === null || $message === '') {
            return '';
        }
        return '<div class="alert">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
    }

    private static function htmlPage(string $title, string $content, int $status = 200): array
    {
        $body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' — FindOwnn API</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: linear-gradient(160deg, #ecfdf5 0%, #f8fafc 45%, #ffffff 100%);
            color: #0f172a; display: flex; align-items: center; justify-content: center; padding: 24px;
        }
        .wrap {
            width: 100%; max-width: 440px; background: #fff; border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08); padding: 28px 24px 24px;
            border: 1px solid #e2e8f0;
        }
        .brand { font-size: 13px; font-weight: 700; letter-spacing: .08em; color: #16a34a; text-transform: uppercase; margin-bottom: 8px; }
        h1 { margin: 0 0 6px; font-size: 26px; }
        .sub { margin: 0 0 20px; color: #64748b; font-size: 14px; line-height: 1.5; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; color: #334155; }
        input, textarea {
            width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 12px;
            font-size: 15px; background: #f8fafc;
        }
        input:focus, textarea:focus { outline: 2px solid #E5EFFB; border-color: #3887C6; background: #fff; }
        button {
            width: 100%; margin-top: 18px; padding: 14px; border: 0; border-radius: 12px;
            background: #3887C6; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer;
        }
        button:hover { background: #16a34a; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .alert {
            background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 12px;
            padding: 12px 14px; font-size: 14px; margin-bottom: 8px;
        }
        .card {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px;
            padding: 14px; margin-bottom: 16px;
        }
        .muted { color: #64748b; font-size: 14px; }
        .token { font-family: ui-monospace, monospace; font-size: 12px; min-height: 88px; }
        .hint { font-size: 12px; color: #64748b; word-break: break-all; }
        .hint code { background: #f1f5f9; padding: 2px 6px; border-radius: 6px; font-size: 11px; }
        .links { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
        .links a { color: #16a34a; font-size: 14px; font-weight: 600; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
        .json-wrap { margin-top: 18px; }
        .json-wrap summary { cursor: pointer; color: #64748b; font-size: 13px; margin-bottom: 8px; }
        pre.json {
            background: #0f172a; color: #e2e8f0; padding: 14px; border-radius: 12px;
            overflow: auto; font-size: 12px; line-height: 1.45;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">FindOwnn API</div>
        ' . $content . '
    </div>
</body>
</html>';

        return [
            'status' => $status,
            'content_type' => 'text/html',
            'body' => $body,
        ];
    }

    /**
     * Register new user
     */
    private static function register($data)
    {
        // Validate
        $errors = [];

        if (empty($data['name'])) $errors['name'] = ['Name is required'];
        if (empty($data['email'])) $errors['email'] = ['Email is required'];
        if (empty($data['phone'])) $errors['phone'] = ['Phone is required'];
        if (empty($data['password'])) $errors['password'] = ['Password is required'];
        if (strlen($data['password'] ?? '') < 8) $errors['password'] = ['Password must be at least 8 characters'];
        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $errors['password'] = ['Passwords do not match'];
        }

        // Check if email exists
        if (self::$db->fetch("SELECT id FROM users WHERE email = ?", [$data['email']])) {
            $errors['email'] = ['Email already exists'];
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
                'status' => 422
            ];
        }

        // Create user
        $token = bin2hex(random_bytes(32));
        $userId = self::$db->insert(
            "INSERT INTO users (name, email, phone, password, whatsapp_number, whatsapp_opt_in, api_token, role, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'player', 'active', NOW(), NOW())",
            [
                $data['name'],
                $data['email'],
                $data['phone'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['whatsapp_number'] ?? null,
                $data['whatsapp_opt_in'] ?? 0,
                $token
            ]
        );

        $user = self::$db->fetch("SELECT id, name, email, phone, role, whatsapp_opt_in, created_at FROM users WHERE id = ?", [$userId]);

        return self::success([
            'user' => $user,
            'token' => $token,
            'expires_at' => date('c', strtotime('+24 hours'))
        ], 'Registration successful', 201);
    }

    /**
     * Login user
     */
    private static function login($data)
    {
        if (empty($data['email']) || empty($data['password'])) {
            return self::error('Email and password are required', 422, 'VALIDATION_ERROR');
        }

        $email = strtolower(trim((string) $data['email']));

        $user = self::$db->fetch(
            "SELECT * FROM users WHERE LOWER(TRIM(email)) = ? AND deleted_at IS NULL LIMIT 1",
            [$email]
        );

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return self::error('Invalid credentials', 401, 'AUTH_001');
        }

        if ($user['status'] !== 'active') {
            return self::error('Account is inactive', 403, 'AUTH_004');
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        self::$db->execute(
            "UPDATE users SET api_token = ?, updated_at = NOW() WHERE id = ?",
            [$token, $user['id']]
        );

        unset($user['password']);

        return self::success([
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role']
            ],
            'token' => $token,
            'expires_at' => date('c', strtotime('+24 hours'))
        ], 'Login successful');
    }

    /**
     * Logout user
     */
    private static function logout(array $body = [])
    {
        if (empty(self::$user) && !empty($body['token'])) {
            self::$user = self::verifyToken($body['token']);
        }

        self::requireAuth();

        self::$db->execute(
            "UPDATE users SET api_token = NULL, updated_at = NOW() WHERE id = ?",
            [self::$user['id']]
        );

        return self::success([], 'Logged out successfully');
    }

    /**
     * Refresh token
     */
    private static function refresh()
    {
        self::requireAuth();

        $newToken = bin2hex(random_bytes(32));
        self::$db->execute(
            "UPDATE users SET api_token = ?, updated_at = NOW() WHERE id = ?",
            [$newToken, self::$user['id']]
        );

        return self::success([
            'token' => $newToken,
            'expires_at' => date('c', strtotime('+24 hours'))
        ], 'Token refreshed');
    }
}
