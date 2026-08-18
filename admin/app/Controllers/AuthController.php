<?php



namespace App\Controllers;



use App\Core\Controller;

use App\Core\Request;

use App\Core\Session;

use App\Models\User;

use App\Models\Subscription;

use App\Models\ActivityLog;

use App\Models\AuditLog;



class AuthController extends Controller

{

    // ── Venue Owner Portal ────────────────────────────────────────



    public function showOwnerLogin(Request $request): void

    {

        $this->render('auth.login-owner', [

            'title' => 'Owner Sign In — Findownn',

            'prefillEmail' => User::normalizeEmail($request->query('email', '')),
        ], 'auth');

    }



    public function ownerLogin(Request $request): void

    {

        $this->authenticate($request, ['venue_owner'], url('/owner/login'), function (array $user) {
            $subModel  = new Subscription();
            $activeSub = $subModel->getActiveByUser($user['id']);

            if (!$activeSub) {
                if ($subModel->assignPlanToUser((int) $user['id'], 'starter', 12)) {
                    ActivityLog::record('Auto-assigned Starter plan on owner login', 'subscription', 'User', (int) $user['id']);
                } else {
                    error_log('[Findownn] Owner login without subscription; plan assign failed for user ' . $user['id']);
                }
            }
        });

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

        $v = new \App\Services\ValidationService();
        $v->minLength($name, 'name', 2, 'Full name')
          ->email($email)
          ->custom(preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone)), 'phone', 'Enter a valid 10-digit Indian mobile number.');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
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



        $userId = $userModel->create([

            'name'              => $name,

            'email'             => $email,

            'password'          => $userModel->hashPassword($password),

            'phone'             => $phone,

            'phone_verified_at' => now(),

            'role'              => 'venue_owner',

            'status'            => 'active',

        ]);



        $subModel = new Subscription();

        if (!$subModel->assignPlanToUser($userId, 'starter', 12)) {

            Session::flash('error', 'Account created but subscription setup failed. Contact ' . site_contact_email());

            $this->redirect(url('/owner/login'));

        }



        AuditLog::log('OWNER_REGISTERED', 'User', $userId, [], ['email' => $email]);

        ActivityLog::record("Venue owner registered: {$email}", 'auth', 'User', $userId);



        Session::flash('success', 'Account created with Starter plan! Sign in to your dashboard.');

        $this->redirect(url('/owner/login'));

    }



    // ── Admin / Staff Portal ──────────────────────────────────────



    public function showAdminLogin(Request $request): void

    {

        $this->render('auth.login-admin', [

            'title' => 'Admin Sign In — Findownn',

        ], 'auth');

    }



    public function adminLogin(Request $request): void

    {

        $this->authenticate($request, ['super_admin', 'admin'], url('/login'));

    }



    // ── Shared ────────────────────────────────────────────────────



    public function logout(Request $request): void

    {

        $user   = Session::get('user') ?? [];

        $userId = $user['id'] ?? 0;

        $role   = $user['role'] ?? '';



        AuditLog::log('LOGOUT', 'User', $userId);

        ActivityLog::record('Logged out.', 'auth', 'User', $userId);

        Session::destroy();



        $this->redirect(url($role === 'venue_owner' ? '/owner/login' : '/login'));

    }



    /**

     * @param array<string> $allowedRoles

     * @param callable(array):void|null $afterRoleCheck

     */

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

            'id'     => $user['id'],

            'name'   => $user['name'],

            'email'  => $user['email'],

            'role'   => $user['role'],

            'avatar' => $user['avatar'] ?? '',

        ]);



        $userModel->updateLastLogin($user['id']);

        ActivityLog::record("Logged in from {$request->ip()}", 'auth', 'User', $user['id']);

        AuditLog::log('LOGIN', 'User', $user['id']);



        Session::flash('show_splash', '1');

        $this->redirect(url('/dashboard'));

    }

}


