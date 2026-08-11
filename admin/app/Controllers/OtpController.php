<?php

namespace App\Controllers;

use App\Contracts\RecordsPhoneVerification;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Services\FirebaseAuthService;
use App\Services\OtpService;

class OtpController extends Controller
{
    public function send(Request $request): void
    {
        try {
            $phone   = trim($request->input('phone', ''));
            $purpose = trim($request->input('purpose', 'registration'));
            $result  = (new OtpService())->send($phone, $purpose);
        } catch (\Throwable $e) {
            $result = [
                'success' => false,
                'message' => 'OTP service unavailable. Please try again later.',
            ];
            error_log('[Findownn OTP] ' . $e->getMessage());
        }

        unset($result['dev_otp'], $result['debug']);

        if ($request->isAjax() || str_contains($request->header('Accept') ?? '', 'application/json')) {
            $this->json($result, $result['success'] ? 200 : 422);
        }

        Session::flash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect($request->header('Referer') ?: url('/owner/register'));
    }

    public function verify(Request $request): void
    {
        $phone = trim($request->input('phone', ''));
        try {
            $code    = trim($request->input('otp', ''));
            $purpose = trim($request->input('purpose', 'registration'));
            $result  = (new OtpService())->verify($phone, $code, $purpose);
        } catch (\Throwable $e) {
            $result = ['success' => false, 'message' => 'OTP verification failed. Please try again.'];
        }

        if ($result['success']) {
            $normalized = (new OtpService())->normalizePhone($phone);
            Session::set('otp_verified_phone', $normalized);
            Session::set('otp_verified_at', time());
        }

        unset($result['dev_otp'], $result['debug']);

        if ($request->isAjax() || str_contains($request->header('Accept') ?? '', 'application/json')) {
            $this->json($result, $result['success'] ? 200 : 422);
        }

        Session::flash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect($request->header('Referer') ?: url('/owner/register'));
    }

    /** Verify OTP after Firebase Phone Auth (Google sends SMS). */
    public function firebaseVerify(Request $request): void
    {
        $phone = trim($request->input('phone', ''));
        try {
            $idToken = trim($request->input('id_token', ''));
            $purpose = trim($request->input('purpose', 'registration'));

            if (!FirebaseAuthService::isConfigured()) {
                $result = ['success' => false, 'message' => 'Firebase OTP is not configured on the server.'];
            } else {
                $check = (new FirebaseAuthService())->verifyIdToken($idToken, $phone);
                if (!$check['success']) {
                    $result = ['success' => false, 'message' => $check['message'] ?? 'Verification failed.'];
                } else {
                    /** @var RecordsPhoneVerification $verification */
                    $verification = new OtpService();
                    $verified = $verification->recordExternalVerification(
                        (string) $check['phone'],
                        $purpose,
                        'firebase'
                    );
                    Session::set('otp_verified_phone', $verified['phone']);
                    Session::set('otp_verified_at', time());
                    $result = [
                        'success' => true,
                        'message' => $verified['message'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            $result = ['success' => false, 'message' => 'OTP verification failed. Please try again.'];
            error_log('[Findownn Firebase OTP] ' . $e->getMessage());
        }

        if ($request->isAjax() || str_contains($request->header('Accept') ?? '', 'application/json')) {
            $this->json($result, $result['success'] ? 200 : 422);
        }

        Session::flash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect($request->header('Referer') ?: url('/owner/register'));
    }
}
