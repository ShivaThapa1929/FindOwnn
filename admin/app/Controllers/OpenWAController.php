<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Services\OpenWAService;
use App\Services\WhatsAppService;

class OpenWAController extends Controller
{
    private function getConfig(): array
    {
        $rows = (new Setting())->getByGroup('whatsapp');
        $cfg  = [];
        foreach ($rows as $row) {
            $cfg[$row['key']] = $row['value'];
        }
        return $cfg;
    }

    private function client(): OpenWAService
    {
        return new OpenWAService($this->getConfig());
    }

    public function index(Request $request): void
    {
        $config   = $this->getConfig();
        $client   = $this->client();
        $health   = $client->healthCheck();
        $session  = $client->isConfigured() ? $client->getSessionStatus() : [];
        $features = $client->getFeatureMatrix();
        $issues   = $client->getConfigDiagnostics();

        $webhookUrl = openwa_webhook_url();

        $this->render('openwa.index', [
            'title'       => 'OpenWA — WhatsApp Gateway',
            'config'      => $config,
            'health'      => $health,
            'session'     => $session,
            'features'    => $features,
            'issues'      => $issues,
            'hasApiKey'   => ($config['openwa_api_key'] ?? '') !== '',
            'webhookUrl'  => $webhookUrl,
            'isLiveSite'  => is_live_site_host(),
            'swaggerUrl'  => $client->isConfigured() ? $client->getSwaggerUrl() : '',
            'dashboardUrl'=> $client->isConfigured() ? $client->getDashboardUrl() : '',
            'success'     => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    public function save(Request $request): void
    {
        $baseUrl = trim((string) $request->input('openwa_base_url', ''));

        if ($baseUrl !== '' && is_live_site_host()) {
            $host = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
            if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                Session::flash('error', 'Live site par localhost OpenWA URL save nahi ho sakti. Pehle Render/Railway par deploy karo, phir public HTTPS URL daalo.');
                $this->redirect(url('/openwa'));
            }
        }

        $keys = [
            'whatsapp_provider',
            'openwa_base_url',
            'openwa_api_key',
            'openwa_session_id',
            'openwa_webhook_secret',
            'send_booking_confirmation',
            'send_payment_confirmation',
            'send_reminder',
            'reminder_hours_before',
        ];

        $secretKeys = ['openwa_api_key', 'openwa_webhook_secret'];

        foreach ($keys as $key) {
            $val = $request->input($key);
            if (in_array($key, $secretKeys, true) && ($val === null || trim((string) $val) === '')) {
                continue;
            }
            if ($val !== null) {
                Setting::setValue($key, $val);
            }
        }

        AuditLog::log('OPENWA_SETTINGS_UPDATED', 'Setting', 0);
        Session::flash('success', 'OpenWA settings saved.');
        $this->redirect(url('/openwa'));
    }

    public function testConnection(Request $request): void
    {
        $client = $this->client();

        if (!$client->isConfigured()) {
            Session::flash('error', 'Configure OpenWA Base URL and API Key first, then click Save OpenWA Settings.');
            $this->redirect(url('/openwa'));
        }

        $health = $client->healthCheck();

        if (!$health['ok']) {
            Session::flash('error', 'Connection failed: ' . ($health['error'] ?? 'Unknown error'));
            $this->redirect(url('/openwa'));
        }

        try {
            $session = $client->ensureSession();
            $status  = strtolower((string) ($session['status'] ?? 'unknown'));

            if ($client->isSessionReady()) {
                Session::flash('success', 'OpenWA connected and WhatsApp session is ready!');
            } else {
                Session::flash(
                    'success',
                    'OpenWA server connected. ' . $client->sessionStatusHint($session)
                );
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'OpenWA reachable but session setup failed: ' . $e->getMessage());
        }

        $this->redirect(url('/openwa'));
    }

    public function setupWebhook(Request $request): void
    {
        $client = $this->client();
        $config = $this->getConfig();

        if (!$client->isConfigured()) {
            Session::flash('error', 'OpenWA not configured.');
            $this->redirect(url('/openwa'));
        }

        $webhookUrl = openwa_webhook_url();

        try {
            $client->ensureSession();
            $client->setupWebhook(
                $webhookUrl,
                ['message.received', 'message.status', 'session.status'],
                $config['openwa_webhook_secret'] ?? null
            );
            ActivityLog::record('OpenWA webhook registered: ' . $webhookUrl, 'openwa');
            Session::flash('success', 'Webhook registered on OpenWA session.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Webhook setup failed: ' . $e->getMessage());
        }

        $this->redirect(url('/openwa'));
    }

    public function sendTest(Request $request): void
    {
        $phone   = trim($request->input('test_phone', ''));
        $message = trim($request->input('test_message', 'Hello from Findownn OpenWA! 🏆'));

        if ($phone === '') {
            Session::flash('error', 'Enter a phone number to send test message.');
            $this->redirect(url('/openwa'));
        }

        $client = $this->client();

        if (!$client->isConfigured()) {
            Session::flash('error', 'OpenWA not configured. Save Base URL and API Key first.');
            $this->redirect(url('/openwa'));
        }

        $issues = $client->getConfigDiagnostics();
        if ($issues !== []) {
            Session::flash('error', $issues[0]);
            $this->redirect(url('/openwa'));
        }

        $health = $client->healthCheck();
        if (!$health['ok']) {
            Session::flash('error', 'Cannot reach OpenWA server: ' . ($health['error'] ?? 'Unknown error'));
            $this->redirect(url('/openwa'));
        }

        if (!$client->isSessionReady()) {
            try {
                $session = $client->ensureSession();
            } catch (\Throwable) {
                $session = $client->getSessionStatus();
            }
            Session::flash('error', $client->sessionStatusHint($session));
            $this->redirect(url('/openwa'));
        }

        $wa     = new WhatsAppService();
        $result = $wa->sendMessage($phone, $message);

        if ($result['success']) {
            Session::flash('success', 'Test message sent via OpenWA!');
        } else {
            Session::flash('error', $result['error'] ?? 'Failed to send test message.');
        }

        $this->redirect(url('/openwa'));
    }
}
