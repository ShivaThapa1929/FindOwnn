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
        $result = [
            'success' => false,
            'message' => 'Mobile OTP is temporarily unavailable.',
        ];

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
        $result = [
            'success' => false,
            'message' => 'Mobile OTP verification is temporarily unavailable.',
        ];

        if ($request->isAjax() || str_contains($request->header('Accept') ?? '', 'application/json')) {
            $this->json($result, $result['success'] ? 200 : 422);
        }

        Session::flash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect($request->header('Referer') ?: url('/owner/register'));
    }
}
