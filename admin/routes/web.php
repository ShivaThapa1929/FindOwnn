<?php

use App\Core\Router;

/** @var Router $router */

// ── OTP (guest + authenticated, CSRF protected) ─────────────────
$router->group(['middleware' => ['csrf']], function (Router $r) {
    $r->post('/otp/send',            ['OtpController', 'send'])->name('otp.send');
    $r->post('/otp/verify',           ['OtpController', 'verify'])->name('otp.verify');
    $r->post('/otp/firebase-verify',  ['OtpController', 'firebaseVerify'])->name('otp.firebase.verify');
});

$router->get('/mail-test', ['AuthController', 'testMail']);

// ── Guest routes ──────────────────────────────────────────────────
$router->group(['middleware' => ['csrf']], function (Router $r) {
    // Venue owner email verification routes (guest & authenticated)
    $r->get('/owner/verify-email',        ['AuthController', 'verifyEmail'])->name('owner.verify.email');
    $r->get('/owner/verify-notice',       ['AuthController', 'showVerifyNotice'])->name('owner.verify.notice');
    $r->post('/owner/resend-verification', ['AuthController', 'resendVerification'])->name('owner.resend.verification');
    $r->post('/owner/change-email',        ['AuthController', 'changeUnverifiedEmail'])->name('owner.change.email');
    $r->post('/owner/direct-verify',       ['AuthController', 'directVerify'])->name('owner.direct.verify');
});

$router->group(['middleware' => ['guest', 'csrf']], function (Router $r) {
    // Venue owner portal
    $r->get('/owner/login',     ['AuthController', 'showOwnerLogin'])->name('owner.login');
    $r->post('/owner/login',    ['AuthController', 'ownerLogin'])->name('owner.login.post');
    $r->get('/owner/register',  ['AuthController', 'showRegisterOwner'])->name('owner.register');
    $r->post('/owner/register', ['AuthController', 'registerOwner'])->name('owner.register.post');

    // Admin / staff portal
    $r->get('/login',  ['AuthController', 'showAdminLogin'])->name('login');
    $r->post('/login', ['AuthController', 'adminLogin'])->name('login.post');
});

// ── Authenticated routes ──────────────────────────────────────────
$router->group(['middleware' => ['auth', 'csrf']], function (Router $r) {

    $r->post('/logout', ['AuthController', 'logout'])->name('logout');

    // Dashboard
    $r->get('/',          ['DashboardController', 'index'])->name('home');
    $r->get('/dashboard', ['DashboardController', 'index'])->name('dashboard');

    // ---- VENUES ------------------------------------------------
    $r->group(['prefix' => '/venues'], function (Router $r) {
        $r->get('/',                   ['VenueController', 'index'])->name('venues');
        $r->get('/create',             ['VenueController', 'create'])->name('venues.create');
        $r->post('/store',             ['VenueController', 'store'])->name('venues.store');
        $r->get('/{id}',               ['VenueController', 'show'])->name('venues.show');
        $r->get('/{id}/edit',          ['VenueController', 'edit'])->name('venues.edit');
        $r->post('/{id}/update',       ['VenueController', 'update'])->name('venues.update');
        $r->post('/{id}/approve',      ['VenueController', 'approve'],      ['role.admin'])->name('venues.approve');
        $r->post('/{id}/reject',       ['VenueController', 'reject'],       ['role.admin'])->name('venues.reject');
        $r->post('/{id}/toggle',       ['VenueController', 'toggleStatus'], ['role.admin'])->name('venues.toggle');
        $r->post('/{id}/badge/assign', ['VenueController', 'assignBadge'],  ['role.admin'])->name('venues.badge.assign');
        $r->post('/{id}/badge/remove', ['VenueController', 'removeBadge'],  ['role.admin'])->name('venues.badge.remove');
        $r->post('/{id}/delete',       ['VenueController', 'destroy'],      ['role.admin'])->name('venues.delete');
    });

    // ---- USERS (admin / super only) ----------------------------
    $r->group(['prefix' => '/users', 'middleware' => ['role.admin']], function (Router $r) {
        $r->get('/',             ['UserController', 'index'])->name('users');
        $r->get('/create',       ['UserController', 'create'])->name('users.create');
        $r->post('/store',       ['UserController', 'store'])->name('users.store');
        $r->get('/{id}',         ['UserController', 'show'])->name('users.show');
        $r->get('/{id}/edit',    ['UserController', 'edit'])->name('users.edit');
        $r->post('/{id}/update', ['UserController', 'update'])->name('users.update');
        $r->post('/{id}/toggle', ['UserController', 'toggleStatus'])->name('users.toggle');
        $r->post('/{id}/delete', ['UserController', 'destroy'], ['role.super'])->name('users.delete');
        // Admin: manually assign subscription to a user
        $r->post('/{id}/assign-sub', ['SubscriptionController', 'adminAssign'], ['role.super'])->name('users.assign-sub');
    });

    // ---- COURTS ------------------------------------------------
    $r->group(['prefix' => '/courts'], function (Router $r) {
        $r->get('/',                ['CourtController', 'index'])->name('courts');
        $r->post('/create',         ['CourtController', 'store'])->name('courts.store');
        $r->get('/{id}/edit',       ['CourtController', 'edit'])->name('courts.edit');
        $r->post('/{id}/update',    ['CourtController', 'update'])->name('courts.update');
        $r->get('/{id}/images',     ['CourtController', 'showImages'])->name('courts.images');
        $r->post('/{id}/delete',    ['CourtController', 'destroy'])->name('courts.delete');
        $r->post('/{id}/status',    ['CourtController', 'updateStatus'])->name('courts.status');
    });

    // ---- SPORTS (admin / super only) ----------------------------
    $r->group(['prefix' => '/sports', 'middleware' => ['role.admin']], function (Router $r) {
        $r->get('/',              ['SportController', 'index'])->name('sports');
        $r->get('/create',        ['SportController', 'create'])->name('sports.create');
        $r->post('/store',        ['SportController', 'store'])->name('sports.store');
        $r->get('/{id}/edit',     ['SportController', 'edit'])->name('sports.edit');
        $r->post('/{id}/update',  ['SportController', 'update'])->name('sports.update');
        $r->post('/{id}/toggle',  ['SportController', 'toggleStatus'])->name('sports.toggle');
        $r->post('/{id}/delete',  ['SportController', 'destroy'], ['role.super'])->name('sports.delete');
    });


    // ---- IMAGE MANAGEMENT --------------------------------------
    $r->group(['prefix' => '/images'], function (Router $r) {
        // Venue images
        $r->post('/venues/upload',        ['ImageController', 'uploadVenueImage'])->name('images.venues.upload');
        $r->post('/venues/{id}/delete',   ['ImageController', 'deleteVenueImage'])->name('images.venues.delete');
        $r->post('/venues/{id}/update',   ['ImageController', 'updateVenueImage'])->name('images.venues.update');
        
        // Court images
        $r->post('/courts/upload',        ['ImageController', 'uploadCourtImage'])->name('images.courts.upload');
        $r->post('/courts/{id}/delete',   ['ImageController', 'deleteCourtImage'])->name('images.courts.delete');
        $r->post('/courts/{id}/update',   ['ImageController', 'updateCourtImage'])->name('images.courts.update');
    });

    // ---- SUBSCRIPTIONS -----------------------------------------
    $r->group(['prefix' => '/subscriptions'], function (Router $r) {
        $r->get('/my-plans',           ['SubscriptionController', 'ownerPlans'])->name('subscriptions.my-plans');
        $r->post('/upgrade',           ['SubscriptionController', 'ownerUpgrade'])->name('subscriptions.upgrade');
        $r->get('/',                   ['SubscriptionController', 'index'], ['role.admin'])->name('subscriptions');
        $r->get('/plans',              ['SubscriptionController', 'plans'], ['role.admin'])->name('subscriptions.plans');
        $r->get('/plans/create',       ['SubscriptionController', 'createPlan'],  ['role.super'])->name('subscriptions.plans.create');
        $r->post('/plans/store',       ['SubscriptionController', 'storePlan'],   ['role.super'])->name('subscriptions.plans.store');
        $r->get('/plans/{id}/edit',    ['SubscriptionController', 'editPlan'],    ['role.super'])->name('subscriptions.plans.edit');
        $r->post('/plans/{id}/update', ['SubscriptionController', 'updatePlan'],  ['role.super'])->name('subscriptions.plans.update');
        $r->post('/plans/{id}/delete', ['SubscriptionController', 'deletePlan'],  ['role.super'])->name('subscriptions.plans.delete');
        $r->post('/expire-check',      ['SubscriptionController', 'expireCheck'], ['role.admin'])->name('subscriptions.expire');
        $r->post('/{id}/cancel',       ['SubscriptionController', 'cancel'],      ['role.admin'])->name('subscriptions.cancel');
    });

    // ---- PLAYERS (customers / app users) -----------------------
    $r->group(['prefix' => '/players'], function (Router $r) {
        $r->get('/',                  ['PlayerController', 'index'])->name('players');
        $r->get('/{id}',              ['PlayerController', 'show'])->name('players.show');
        $r->post('/{id}/reminder',    ['PlayerController', 'sendReminder'])->name('players.reminder');
    });

    // ---- BOOKINGS ----------------------------------------------
    $r->group(['prefix' => '/bookings'], function (Router $r) {
        $r->get('/',                      ['BookingController', 'index'])->name('bookings');
        $r->get('/slots',                 ['BookingController', 'slots'])->name('bookings.slots');
        $r->get('/offline/create',        ['BookingController', 'createOffline'])->name('bookings.offline.create');
        $r->post('/offline/store',        ['BookingController', 'storeOffline'])->name('bookings.offline.store');
        $r->get('/{id}',                  ['BookingController', 'show'])->name('bookings.show');
        $r->post('/{id}/status',          ['BookingController', 'updateStatus'])->name('bookings.status');
        $r->post('/{id}/payment',         ['BookingController', 'updatePayment'])->name('bookings.payment');
        $r->post('/{id}/reminder',        ['BookingController', 'sendReminder'])->name('bookings.reminder');
    });

    // ---- REPORTS (admin / super only) --------------------------
    $r->group(['prefix' => '/reports', 'middleware' => ['role.admin']], function (Router $r) {
        $r->get('/',           ['ReportController', 'index'])->name('reports');
        $r->get('/audit-logs', ['ReportController', 'auditLogs'])->name('reports.audit');
        $r->get('/activity',   ['ReportController', 'activityLogs'])->name('reports.activity');
    });

    // ---- SETTINGS (super admin only) ---------------------------
    $r->group(['prefix' => '/settings', 'middleware' => ['role.super']], function (Router $r) {
        $r->get('/',        ['SettingsController', 'index'])->name('settings');
        $r->post('/save',   ['SettingsController', 'update'])->name('settings.save');
        $r->post('/backup', ['SettingsController', 'backup'])->name('settings.backup');
        $r->post('/setup-payment', ['SettingsController', 'setupPayment'])->name('settings.setup-payment');
    });

    // ---- CONTACT MESSAGES (super admin) ------------------------
    $r->group(['prefix' => '/contact-messages', 'middleware' => ['role.super']], function (Router $r) {
        $r->get('/',              ['ContactMessageController', 'index'])->name('contact-messages');
        $r->post('/{id}/read',    ['ContactMessageController', 'markRead'])->name('contact-messages.read');
    });

    // ---- API ---------------------------------------------------
    $r->get('/api/courts', ['CourtController', 'apiGetCourts'])->name('api.courts');

    // ---- PROFILE -----------------------------------------------
    $r->get('/profile',         ['ProfileController', 'show'])->name('profile');
    $r->post('/profile/update', ['ProfileController', 'update'])->name('profile.update');
});
