<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Subscription;
use App\Models\User;
use App\Services\DisposableEmailChecker;
use App\Services\EmailVerificationService;
use App\Services\ValidationService;

class AuthController extends Controller
{
    public function showAdminLogin(Request $request): void
    {
        $this->render('auth.login-admin', array_merge([
            'title' => 'Admin Sign In — FindOwnn',
        ], firebase_otp_context()), 'auth');
    }

    public function adminLogin(Request $request): void
    {
        $this->authenticate($request, ['super_admin', 'admin'], url('/login'));
    }

    public function showOwnerLogin(Request $request): void
    {
        $prefillEmail = trim($request->query('email', ''));

        $this->render('auth.login-owner', array_merge([
            'title' => 'Venue Owner Login — FindOwnn',
            'prefillEmail' => $prefillEmail,
        ], firebase_otp_context()), 'auth');
    }

    public function ownerLogin(Request $request): void
    {
        $this->authenticate($request, ['venue_owner'], url('/owner/login'));
    }

    public function showRegisterOwner(Request $request): void
    {
        $this->render('auth.register-owner', array_merge([
            'title' => 'Register — Venue Owner',
        ], firebase_otp_context()), 'auth');
    }

    public function registerOwner(Request $request): void
    {
        $name     = trim($request->input('name', ''));
        $email    = User::normalizeEmail($request->raw('email', ''));
        $phone    = trim($request->input('phone', ''));
        $password = $request->raw('password', '');
        $confirm  = $request->raw('password_confirm', '');

        if ($name === '' || $email === '' || $phone === '' || $password === '') {
            Session::flash('error', 'All fields are required.');
            $this->redirect(url('/owner/register'));
        }

        if (str_contains($email, ' ')) {
            Session::flash('error', 'Email address cannot contain spaces.');
            $this->redirect(url('/owner/register'));
        }

        $v = new ValidationService();
        $v->minLength($name, 'name', 2, 'Full name')
          ->email($email)
          ->custom(preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone)), 'phone', 'Enter a valid 10-digit Indian mobile number.');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/owner/register'));
        }

        // Check disposable email
        if (DisposableEmailChecker::isDisposable($email)) {
            Session::flash('error', 'Please use a valid permanent email address that you can access.');
            $this->redirect(url('/owner/register'));
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            $this->redirect(url('/owner/register'));
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            $this->redirect(url('/owner/register'));
        }

        $cleanPhone = preg_replace('/\D/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $phone = '+91 ' . substr($cleanPhone, 0, 5) . ' ' . substr($cleanPhone, 5);
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            Session::flash('error', 'This email is already registered. Please sign in instead.');
            $this->redirect(url('/owner/login'));
        }

        // Create pending user account
        $userId = $userModel->create([
            'name'              => $name,
            'email'             => $email,
            'password'          => $userModel->hashPassword($password),
            'phone'             => $phone,
            'phone_verified_at' => now(),
            'role'              => 'venue_owner',
            'status'            => 'pending_email_verification',
            'email_verified_at' => null,
        ]);

        $subModel = new Subscription();
        if (!$subModel->assignPlanToUser($userId, 'starter', 12)) {
            Session::flash('error', 'Account created but subscription setup failed. Contact ' . site_contact_email());
            $this->redirect(url('/owner/login'));
        }

        AuditLog::log('OWNER_REGISTERED_PENDING_VERIFICATION', 'User', $userId, [], ['email' => $email]);
        ActivityLog::record("Venue owner registered (pending verification): {$email}", 'auth', 'User', $userId);

        // Generate verification token and send email
        $verifyService = new EmailVerificationService();
        $rawToken = $verifyService->createVerificationToken((int)$userId);
        $verifyService->sendVerificationEmail([
            'id'    => $userId,
            'name'  => $name,
            'email' => $email
        ], $rawToken);

        $_SESSION['unverified_email'] = $email;
        Session::flash('success', 'Registration submitted! Please check your email and click the verification link to activate your Venue Owner account.');
        $this->redirect(url('/owner/verify-notice?email=' . urlencode($email)));
    }

    public function showVerifyNotice(Request $request): void
    {
        $email = trim($request->query('email', '')) ?: ($_SESSION['unverified_email'] ?? '');
        $user = null;
        if ($email !== '') {
            $userModel = new User();
            $user = $userModel->findByEmail($email);
        }

        $this->render('auth.verify-notice', [
            'title'          => 'Verify Email — FindOwnn',
            'email'          => $email,
            'unverifiedUser' => $user
        ], 'auth');
    }

    public function verifyEmail(Request $request): void
    {
        $token = trim($request->query('token', ''));
        $verifyService = new EmailVerificationService();
        $res = $verifyService->verifyEmailToken($token);

        if (!empty($res['success']) && !empty($res['user'])) {
            $user = $res['user'];
            
            Session::regenerate();
            Session::set('user', [
                'id'                => (int)$user['id'],
                'name'              => $user['name'],
                'email'             => $user['email'],
                'role'              => $user['role'] ?? 'venue_owner',
                'email_verified_at' => $user['email_verified_at'] ?? date('Y-m-d H:i:s'),
                'status'            => 'active',
                'avatar'            => $user['avatar'] ?? '',
            ]);

            $userModel = new User();
            $userModel->updateLastLogin((int)$user['id']);

            try {
                ActivityLog::record("Logged in automatically after email verification", 'auth', 'User', (int)$user['id']);
                AuditLog::log('EMAIL_VERIFIED_AUTO_LOGIN', 'User', (int)$user['id']);
            } catch (\Throwable $e) {}

            unset($_SESSION['unverified_email']);
            Session::flash('show_splash', '1');
            Session::flash('success', 'Email verified successfully! Welcome to your Venue Owner Dashboard.');
            $this->redirect(url('/dashboard'));
            return;
        }

        $this->render('auth.verify-result', [
            'title'   => 'Email Verification — FindOwnn',
            'status'  => strtolower($res['code'] ?? '') === 'expired_token' ? 'expired' : 'invalid',
            'message' => $res['message'],
            'user'    => $res['user'] ?? null
        ], 'auth');
    }

    public function resendVerification(Request $request): void
    {
        $email = trim($request->input('email', '')) ?: ($_SESSION['unverified_email'] ?? '');
        if ($email === '') {
            Session::flash('error', 'Please enter your email address to resend verification.');
            $this->redirect(url('/owner/verify-notice'));
        }

        $verifyService = new EmailVerificationService();
        $res = $verifyService->resendVerification($email);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect(url('/owner/verify-notice?email=' . urlencode($email)));
    }

    public function changeUnverifiedEmail(Request $request): void
    {
        $userId = (int)$request->input('user_id', 0);
        $newEmail = trim($request->input('new_email', ''));

        if (!$userId && Session::has('user')) {
            $userId = (int)Session::get('user')['id'];
        }

        if (!$userId && !empty($_SESSION['unverified_email'])) {
            $userModel = new User();
            $u = $userModel->findByEmail($_SESSION['unverified_email']);
            if ($u) $userId = (int)$u['id'];
        }

        if (!$userId) {
            Session::flash('error', 'Session expired. Please register again or contact support.');
            $this->redirect(url('/owner/register'));
        }

        $verifyService = new EmailVerificationService();
        $res = $verifyService->changeUnverifiedEmail($userId, $newEmail);

        if ($res['success']) {
            $_SESSION['unverified_email'] = $newEmail;
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect(url('/owner/verify-notice?email=' . urlencode($newEmail)));
    }

    public function directVerify(Request $request): void
    {
        $userId = (int)$request->input('user_id', 0);
        if (!$userId && !empty($_SESSION['unverified_email'])) {
            $userModel = new User();
            $u = $userModel->findByEmail($_SESSION['unverified_email']);
            if ($u) $userId = (int)$u['id'];
        }

        if (!$userId) {
            Session::flash('error', 'User account not found. Please register again.');
            $this->redirect(url('/owner/register'));
            return;
        }

        $userModel = new User();
        $db = \App\Core\Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

        if (!$user) {
            Session::flash('error', 'User account not found.');
            $this->redirect(url('/owner/register'));
            return;
        }

        // Activate user directly
        $db->execute("UPDATE users SET email_verified_at = NOW(), status = 'active', email_verification_token_hash = NULL, email_verification_expires_at = NULL, updated_at = NOW() WHERE id = ?", [$userId]);

        Session::regenerate();
        Session::set('user', [
            'id'                => (int)$user['id'],
            'name'              => $user['name'],
            'email'             => $user['email'],
            'role'              => $user['role'] ?? 'venue_owner',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'status'            => 'active',
            'avatar'            => $user['avatar'] ?? '',
        ]);

        $userModel->updateLastLogin((int)$user['id']);
        unset($_SESSION['unverified_email']);
        Session::flash('show_splash', '1');
        Session::flash('success', 'Account verified & activated successfully! Welcome to your Venue Owner Dashboard.');
        $this->redirect(url('/dashboard'));
    }

    public function testMail(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $to = trim($request->query('to', 'shivathapa1929@gmail.com'));

        $host = $_SERVER['HTTP_HOST'] ?? 'findownn.com';
        $host = preg_replace('/:\d+$/', '', $host);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: FindOwnn <no-reply@' . $host . '>',
            'Reply-To: support@' . $host,
            'X-Mailer: PHP/' . phpversion()
        ];

        $subject = 'FindOwnn Live Mail Test — ' . date('H:i:s');
        $body = '<h3>FindOwnn Mail Test</h3><p>If you see this email, mail sending is working 100% on your Hostinger server!</p>';

        error_clear_last();
        $ok = @mail($to, $subject, $body, implode("\r\n", $headers));
        $lastErr = error_get_last();

        echo json_encode([
            'status'     => $ok ? 'success' : 'failed',
            'recipient'  => $to,
            'from'       => 'no-reply@' . $host,
            'timestamp'  => date('Y-m-d H:i:s'),
            'last_error' => $lastErr['message'] ?? null
        ], JSON_PRETTY_PRINT);
        exit;
    }

    public function logout(): void
    {
        $user = Session::get('user');
        $role = $user['role'] ?? 'venue_owner';

        if ($user) {
            ActivityLog::record('Logged out', 'auth', 'User', $user['id']);
            AuditLog::log('LOGOUT', 'User', $user['id']);
        }

        Session::destroy();
        $this->redirect(url($role === 'venue_owner' ? '/owner/login' : '/login'));
    }

    private function authenticate(Request $request, array $allowedRoles, string $failRedirect, ?callable $afterRoleCheck = null): void
    {
        $email    = User::normalizeEmail($request->raw('email', ''));
        $password = $request->raw('password', '');

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email and password are required.');
            $this->redirect($failRedirect);
        }

        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        if (!$user) {
            AuditLog::log('FAILED_LOGIN', 'User', 0, ['email' => $email]);
            Session::flash('error', 'No account found for this email. Check spelling or <a href="' . url('/owner/register') . '" class="alert-link">create an owner account</a>.');
            $this->redirect($failRedirect);
        }

        if (!$userModel->verifyPassword($password, $user['password'])) {
            AuditLog::log('FAILED_LOGIN', 'User', (int) $user['id'], ['email' => $email]);
            Session::flash('error', 'Incorrect password for this email. Try again or reset your password.');
            $this->redirect($failRedirect);
        }

        // Check if Venue Owner email is unverified
        if ($user['role'] === 'venue_owner' && (empty($user['email_verified_at']) || $user['status'] === 'pending_email_verification')) {
            $_SESSION['unverified_email'] = $email;
            Session::flash('error', 'Please verify your email address before accessing your Venue Owner Dashboard.');
            $this->redirect(url('/owner/verify-notice?email=' . urlencode($email)));
        }

        if ($user['status'] !== 'active') {
            Session::flash('error', 'Your account has been suspended. Please contact support.');
            $this->redirect($failRedirect);
        }

        if (!in_array($user['role'], $allowedRoles, true)) {
            AuditLog::log('LOGIN_WRONG_PORTAL', 'User', $user['id'], ['email' => $email, 'role' => $user['role']]);

            if (in_array($user['role'], ['super_admin', 'admin'], true)) {
                Session::flash('error', 'Staff accounts must use the <a href="' . url('/login') . '" class="alert-link">Admin login page</a>.');
                $this->redirect(url('/owner/login'));
            }

            if ($user['role'] === 'venue_owner') {
                Session::flash('error', 'Venue owner account — sign in at the <a href="' . url('/owner/login') . '?email=' . urlencode($email) . '" class="alert-link">Owner portal</a>.');
                $this->redirect(url('/owner/login') . '?email=' . urlencode($email));
            }

            Session::flash('error', 'Player account — sign in on the <a href="' . site_home_url() . '?auth=login&amp;email=' . urlencode($email) . '" class="alert-link">Findownn website</a>.');
            $this->redirect($failRedirect);
        }

        if ($afterRoleCheck) {
            $afterRoleCheck($user);
        }

        Session::regenerate();
        Session::set('user', [
            'id'                => $user['id'],
            'name'              => $user['name'],
            'email'             => $user['email'],
            'role'              => $user['role'],
            'email_verified_at' => $user['email_verified_at'] ?? null,
            'status'            => $user['status'] ?? 'active',
            'avatar'            => $user['avatar'] ?? '',
        ]);

        $userModel->updateLastLogin($user['id']);
        ActivityLog::record("Logged in from {$request->ip()}", 'auth', 'User', $user['id']);
        AuditLog::log('LOGIN', 'User', $user['id']);

        Session::flash('show_splash', '1');
        $this->redirect(url('/dashboard'));
    }
}
