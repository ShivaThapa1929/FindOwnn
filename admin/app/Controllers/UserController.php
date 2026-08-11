<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Services\ValidationService;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function index(Request $request): void
    {
        $page   = (int) $request->query('page', 1);
        $role   = $request->query('role', 'all');
        $search = $request->query('search', '');

        if ($search) {
            $result = $this->userModel->search($search, $page);
        } elseif ($role !== 'all') {
            $result = $this->userModel->getByRole($role, $page);
        } else {
            $result = $this->userModel->paginate($page, 20, 'deleted_at IS NULL', [], 'created_at DESC');
        }

        $this->render('users.index', [
            'title'   => 'Manage Users',
            'result'  => $result,
            'role'    => $role,
            'search'  => $search,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    public function show(Request $request): void
    {
        $id   = (int) $request->param('id');
        $user = $this->userModel->findOrFail($id);

        $this->render('users.show', [
            'title'    => 'User: ' . e($user['name']),
            'userItem' => $user,
            'db'       => $this->db,
        ]);
    }

    public function create(Request $request): void
    {
        $freePlan = $this->db->fetch(
            "SELECT * FROM subscription_plans WHERE slug IN ('starter', 'free') AND is_active = 1 ORDER BY FIELD(slug, 'starter', 'free') LIMIT 1"
        ) ?: $this->db->fetch(
            "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 1"
        );

        $this->render('users.create', [
            'title'    => 'Create Admin/User',
            'freePlan' => $freePlan,
        ]);
    }

    public function store(Request $request): void
    {
        $email    = trim(strtolower($request->input('email', '')));
        $phone    = trim($request->input('phone', ''));
        $role     = $request->input('role', 'venue_owner');
        $password = $request->raw('password', '');
        $confirm  = $request->raw('password_confirm', '');

        $allowedRoles = isRole('super_admin')
            ? ['venue_owner', 'admin', 'super_admin']
            : ['venue_owner', 'admin'];

        $v = new ValidationService();
        $v->required($request->input('name'), 'name', 'Full name')
          ->minLength($request->input('name', ''), 'name', 2, 'Full name')
          ->required($email, 'email', 'Email')
          ->email($email)
          ->required($phone, 'phone', 'Phone')
          ->custom(preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone)), 'phone', 'Enter a valid 10-digit Indian mobile number.')
          ->required($password, 'password', 'Password')
          ->minLength($password, 'password', 8, 'Password')
          ->in($role, 'role', $allowedRoles, 'Role')
          ->custom($password === $confirm, 'password_confirm', 'Passwords do not match.');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/users/create'));
        }

        $otpService = new \App\Services\OtpService();
        if (!$otpService->isVerifiedRecently($phone, 'registration')) {
            Session::flash('error', 'Phone OTP verification required. Send and verify OTP first.');
            $this->redirect(url('/users/create'));
        }

        if ($this->userModel->findByEmail($email)) {
            Session::flash('error', 'Email already exists.');
            $this->redirect(url('/users/create'));
        }

        $cleanPhone = preg_replace('/\D/', '', $phone);
        $phone = '+91 ' . substr($cleanPhone, 0, 5) . ' ' . substr($cleanPhone, 5);

        $id = $this->userModel->create([
            'name'              => $request->input('name'),
            'email'             => $email,
            'password'          => $this->userModel->hashPassword($password),
            'phone'             => $phone,
            'phone_verified_at' => now(),
            'role'              => $role,
            'status'            => 'active',
        ]);

        if ($role === 'venue_owner') {
            $assigned = (new \App\Models\Subscription())->assignPlanToUser($id, 'starter', 12);
            if (!$assigned) {
                Session::flash('error', 'User created but Starter plan assignment failed. Assign manually from user profile.');
                $this->redirect(url('/users/' . $id));
            }
        }

        AuditLog::log('USER_CREATED', 'User', $id, [], ['email' => $email]);
        ActivityLog::record("Created user: {$email}", 'user', 'User', $id);
        Session::flash('success', $role === 'venue_owner'
            ? 'Venue owner created with Starter plan. They can sign in now.'
            : 'User created successfully.');
        $this->redirect(url('/users/' . $id));
    }

    public function edit(Request $request): void
    {
        $id   = (int) $request->param('id');
        $user = $this->userModel->findOrFail($id);
        $this->render('users.edit', ['title' => 'Edit User', 'userItem' => $user]);
    }

    public function update(Request $request): void
    {
        $id  = (int) $request->param('id');
        $old = $this->userModel->findOrFail($id);

        $allowedRoles = isRole('super_admin')
            ? ['venue_owner', 'admin', 'super_admin']
            : ['venue_owner', 'admin'];

        $v = new ValidationService();
        $v->required($request->input('name'), 'name', 'Full name')
          ->minLength($request->input('name', ''), 'name', 2, 'Full name')
          ->in($request->input('role'), 'role', $allowedRoles, 'Role');

        $phone = trim($request->input('phone', ''));
        if ($phone !== '') {
            $v->custom(preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone)), 'phone', 'Enter a valid 10-digit mobile number.');
        }

        $newPass = $request->raw('password', '');
        $confirm = $request->raw('password_confirm', '');
        if ($newPass !== '') {
            $v->minLength($newPass, 'password', 8, 'Password')
              ->custom($newPass === $confirm, 'password_confirm', 'Passwords do not match.');
        }

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/users/' . $id . '/edit'));
        }

        $data = [
            'name'  => trim($request->input('name')),
            'phone' => $phone,
            'role'  => $request->input('role'),
        ];

        if ($newPass !== '') {
            $data['password'] = $this->userModel->hashPassword($newPass);
        }

        $this->userModel->update($id, $data);
        AuditLog::log('USER_UPDATED', 'User', $id, $old, $data);
        ActivityLog::record("Updated user: {$old['email']}", 'user', 'User', $id);
        Session::flash('success', 'User updated.');
        $this->redirect(url('/users'));
    }

    public function toggleStatus(Request $request): void
    {
        $id   = (int) $request->param('id');
        $user = $this->userModel->findOrFail($id);
        $this->userModel->toggleStatus($id);

        AuditLog::log('USER_STATUS_TOGGLED', 'User', $id, ['status' => $user['status']]);
        ActivityLog::record("Toggled status for user: {$user['email']}", 'user', 'User', $id);

        if ($request->isAjax()) {
            $this->json(['success' => true]);
        }
        Session::flash('success', 'User status updated.');
        $this->redirect(url('/users'));
    }

    public function destroy(Request $request): void
    {
        $id   = (int) $request->param('id');
        $user = $this->userModel->findOrFail($id);

        // Prevent self-deletion
        if ($id == $this->user()['id']) {
            Session::flash('error', 'You cannot delete your own account.');
            $this->redirect(url('/users'));
        }

        $this->userModel->softDelete($id);
        AuditLog::log('USER_DELETED', 'User', $id);
        ActivityLog::record("Deleted user: {$user['email']}", 'user', 'User', $id);
        Session::flash('success', 'User deleted.');
        $this->redirect(url('/users'));
    }
}
