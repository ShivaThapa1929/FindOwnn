<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    public function show(Request $request): void
    {
        $user = $this->db->fetch(
            "SELECT u.*, s.status AS sub_status, p.name AS plan_name, s.expires_at
             FROM users u
             LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status = 'active'
             LEFT JOIN subscription_plans p ON s.plan_id = p.id
             WHERE u.id = ?",
            [$this->user()['id']]
        );

        $this->render('profile.show', [
            'title'    => 'My Profile',
            'userItem' => $user,
            'db'       => $this->db,
        ]);
    }

    public function update(Request $request): void
    {
        $id  = $this->user()['id'];
        $old = (new User())->find($id);

        $data = [
            'name'  => $request->input('name'),
            'phone' => $request->input('phone'),
        ];

        if (empty($data['name'])) {
            Session::flash('error', 'Name cannot be empty.');
            $this->redirect(url('/profile'));
        }

        $newPass = $request->raw('password', '');
        $confirm = $request->raw('password_confirm', '');
        if ($newPass !== '') {
            if (strlen($newPass) < 8) {
                Session::flash('error', 'Password must be at least 8 characters.');
                $this->redirect(url('/profile'));
            }
            if ($newPass !== $confirm) {
                Session::flash('error', 'Password confirmation does not match.');
                $this->redirect(url('/profile'));
            }
            $data['password'] = (new User())->hashPassword($newPass);
        }

        (new User())->update($id, $data);

        // Refresh session name
        $_SESSION['user']['name'] = $data['name'];

        AuditLog::log('PROFILE_UPDATED', 'User', $id, $old, $data);
        ActivityLog::record('Updated profile.', 'user', 'User', $id);

        Session::flash('success', 'Profile updated successfully.');
        $this->redirect(url('/profile'));
    }
}
