<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class SubscriptionController extends Controller
{
    public function index(Request $request): void
    {
        $page   = (int) $request->query('page', 1);
        $status = $request->query('status', 'all');
        $subModel = new Subscription();

        $result = $subModel->getAllWithDetails($page, 20, $status);
        $stats  = $subModel->getStats();
        $plans  = (new SubscriptionPlan())->getActivePlans();

        $this->render('subscriptions.index', [
            'title'   => 'Subscriptions',
            'result'  => $result,
            'stats'   => $stats,
            'plans'   => $plans,
            'status'  => $status,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    public function plans(Request $request): void
    {
        $plans = (new SubscriptionPlan())->all('sort_order', 'ASC');
        $this->render('subscriptions.plans', [
            'title'   => 'Subscription Plans',
            'plans'   => $plans,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    public function createPlan(Request $request): void
    {
        $this->render('subscriptions.create-plan', ['title' => 'Create Plan']);
    }

    public function storePlan(Request $request): void
    {
        $planModel = new SubscriptionPlan();
        $name = $request->input('name');
        $id = $planModel->create([
            'name'          => $name,
            'slug'          => slugify($name),
            'price'         => (float) $request->input('price', 0),
            'platform_fee_percent' => $request->input('platform_fee_percent') !== '' && $request->input('platform_fee_percent') !== null
                ? (float) $request->input('platform_fee_percent')
                : null,
            'billing_cycle' => $request->input('billing_cycle', 'monthly'),
            'description'   => $request->raw('description'),
            'features'      => $request->raw('features'),
            'max_venues'    => (int) $request->input('max_venues', 1),
            'is_active'     => (int) $request->input('is_active', 1),
            'is_featured'   => (int) $request->input('is_featured', 0),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ]);

        AuditLog::log('PLAN_CREATED', 'SubscriptionPlan', $id, [], ['name' => $name]);
        ActivityLog::record("Created subscription plan: {$name}", 'subscription');
        Session::flash('success', 'Subscription plan created.');
        $this->redirect(url('/subscriptions/plans'));
    }

    public function editPlan(Request $request): void
    {
        $id   = (int) $request->param('id');
        $plan = (new SubscriptionPlan())->findOrFail($id);
        $this->render('subscriptions.edit-plan', ['title' => 'Edit Plan', 'plan' => $plan]);
    }

    public function updatePlan(Request $request): void
    {
        $id        = (int) $request->param('id');
        $planModel = new SubscriptionPlan();
        $old       = $planModel->findOrFail($id);

        $name = $request->input('name');
        $data = [
            'name'          => $name,
            'price'         => (float) $request->input('price', 0),
            'platform_fee_percent' => $request->input('platform_fee_percent') !== '' && $request->input('platform_fee_percent') !== null
                ? (float) $request->input('platform_fee_percent')
                : null,
            'billing_cycle' => $request->input('billing_cycle', 'monthly'),
            'description'   => $request->raw('description'),
            'features'      => $request->raw('features'),
            'max_venues'    => (int) $request->input('max_venues', 1),
            'is_active'     => (int) $request->input('is_active', 1),
            'is_featured'   => (int) $request->input('is_featured', 0),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ];

        $planModel->update($id, $data);
        AuditLog::log('PLAN_UPDATED', 'SubscriptionPlan', $id, $old, $data);
        Session::flash('success', 'Plan updated.');
        $this->redirect(url('/subscriptions/plans'));
    }

    public function expireCheck(Request $request): void
    {
        $count = (new Subscription())->expireOld();
        $this->json(['expired' => $count, 'message' => "{$count} subscriptions marked as expired."]);
    }

    /** Admin: manually assign a subscription to a venue owner */
    public function adminAssign(Request $request): void
    {
        $userId = (int) $request->param('id');
        $planId = (int) $request->input('plan_id', 0);
        $months = max(1, (int) $request->input('months', 1));

        $user = (new \App\Models\User())->find($userId);
        if (!$user) {
            Session::flash('error', 'User not found.');
            $this->redirect(url('/users'));
        }
        if ($user['role'] !== 'venue_owner') {
            Session::flash('error', 'Subscriptions can only be assigned to venue owners.');
            $this->redirect(url('/users/' . $userId));
        }

        $v = new \App\Services\ValidationService();
        $v->custom($planId > 0, 'plan_id', 'Please select a valid plan.')
          ->custom($months >= 1 && $months <= 24, 'months', 'Duration must be 1–24 months.');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            $this->redirect(url('/users/' . $userId));
        }

        $plan = (new SubscriptionPlan())->find($planId);
        if (!$plan) {
            Session::flash('error', 'Plan not found.');
            $this->redirect(url('/users/' . $userId));
        }

        $subModel = new Subscription();
        $subId    = $subModel->replaceActiveSubscription($userId, $planId, $months, 0, 'active');

        if (!$subId) {
            Session::flash('error', 'Failed to assign subscription. Check that plans exist in the database.');
            $this->redirect(url('/users/' . $userId));
        }

        AuditLog::log('SUBSCRIPTION_ASSIGNED', 'Subscription', (int) $subId, [],
            ['plan' => $plan['name'], 'months' => $months, 'user_id' => $userId]);
        ActivityLog::record(
            "Admin assigned {$plan['name']} plan ({$months}mo) to user #{$userId}",
            'subscription', 'User', $userId
        );

        Session::flash('success', "{$plan['name']} plan assigned for {$months} month(s).");
        $this->redirect(url('/users/' . $userId));
    }

    /** Venue owner: view plans and upgrade */
    public function ownerPlans(Request $request): void
    {
        if ($this->user()['role'] !== 'venue_owner') {
            $this->redirect(url('/dashboard'));
        }

        $subModel = new Subscription();
        $plans    = (new SubscriptionPlan())->getActivePlans();
        $mySub    = $subModel->getActiveByUser($this->user()['id']);

        $this->render('subscriptions.owner-plans', [
            'title'   => 'My Subscription Plans',
            'plans'   => $plans,
            'mySub'   => $mySub,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    /** Venue owner: upgrade / switch plan */
    public function ownerUpgrade(Request $request): void
    {
        if ($this->user()['role'] !== 'venue_owner') {
            Session::flash('error', 'Access denied.');
            $this->redirect(url('/dashboard'));
        }

        $planId = (int) $request->input('plan_id', 0);
        $userId = (int) $this->user()['id'];
        $plan   = (new SubscriptionPlan())->find($planId);

        if (!$plan || !(int) ($plan['is_active'] ?? 0)) {
            Session::flash('error', 'Invalid plan selected.');
            $this->redirect(url('/dashboard'));
        }

        $current = (new Subscription())->getActiveByUser($userId);
        if ($current && (int) $current['plan_id'] === $planId) {
            Session::flash('error', 'You are already on the ' . $plan['name'] . ' plan.');
            $this->redirect(url('/dashboard'));
        }

        $price  = (float) ($plan['price'] ?? 0);
        $months = ($plan['billing_cycle'] ?? '') === 'yearly' ? 12 : 1;
        $slug   = (string) ($plan['slug'] ?? '');

        if ($slug === 'enterprise') {
            ActivityLog::record("Enterprise plan enquiry from user #{$userId}", 'subscription', 'User', $userId);
            Session::flash('success', 'Thank you for your interest in the Enterprise Plan! Our team will contact you shortly.');
            $this->redirect(url('/dashboard'));
            return;
        }

        // Instantly switch and activate the selected plan for the Venue Owner
        $subModel = new Subscription();
        $subId    = $subModel->replaceActiveSubscription($userId, $planId, $months, $price, 'active');

        if (!$subId) {
            Session::flash('error', 'Could not switch plan. Please try again.');
            $this->redirect(url('/dashboard'));
            return;
        }

        AuditLog::log('SUBSCRIPTION_SWITCHED', 'Subscription', (int) $subId, [], ['plan' => $plan['name'], 'price' => $price]);
        ActivityLog::record("Venue owner switched plan to {$plan['name']} (₹" . number_format($price) . ")", 'subscription', 'User', $userId);

        Session::flash('success', "Plan switched successfully! You are now on the {$plan['name']} plan.");
        $this->redirect(url('/dashboard'));
    }

    /** Admin: cancel a subscription */
    public function cancel(Request $request): void
    {
        $id  = (int) $request->param('id');
        $sub = (new Subscription())->find($id);

        if (!$sub) {
            Session::flash('error', 'Subscription not found.');
            $this->redirect(url('/subscriptions'));
        }

        $this->db->execute(
            "UPDATE subscriptions SET status = 'cancelled', updated_at = ? WHERE id = ?",
            [now(), $id]
        );

        \App\Models\AuditLog::log('SUBSCRIPTION_CANCELLED', 'Subscription', $id);
        \App\Models\ActivityLog::record("Subscription #{$id} cancelled", 'subscription', 'Subscription', $id);

        Session::flash('success', 'Subscription cancelled.');
        $this->redirect(url('/subscriptions'));
    }

    /** Super Admin: delete a plan */
    public function deletePlan(Request $request): void
    {
        $id = (int) $request->param('id');

        // Check if plan is in use
        $inUse = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM subscriptions WHERE plan_id = ? AND status = 'active'",
            [$id]
        );

        if ($inUse > 0) {
            Session::flash('error', "Cannot delete plan — {$inUse} active subscription(s) use it.");
            $this->redirect(url('/subscriptions/plans'));
        }

        $this->db->execute("DELETE FROM subscription_plans WHERE id = ?", [$id]);
        \App\Models\AuditLog::log('PLAN_DELETED', 'SubscriptionPlan', $id);
        Session::flash('success', 'Plan deleted.');
        $this->redirect(url('/subscriptions/plans'));
    }
}
