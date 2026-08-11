<?php

namespace App\Services;

use Exception;

/**
 * OpenWA REST API client — https://github.com/rmyndharis/OpenWA
 */
class OpenWAService
{
    private string $baseUrl;
    private string $apiKey;
    private string $sessionId;
    private int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl   = rtrim($config['openwa_base_url'] ?? '', '/');
        $this->apiKey    = $config['openwa_api_key'] ?? '';
        $this->sessionId = $config['openwa_session_id'] ?? 'findownn';
        $this->timeout   = (int) ($config['openwa_timeout'] ?? 30);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    /** Human-readable setup issues for admin UI */
    public function getConfigDiagnostics(): array
    {
        $issues = [];

        if ($this->baseUrl === '') {
            $issues[] = 'OpenWA Base URL is empty — save the server URL (e.g. http://localhost:2785).';
        }

        if ($this->apiKey === '') {
            $issues[] = 'OpenWA API Key is missing — paste the key from OpenWA startup logs and click Save.';
        }

        if ($this->baseUrl !== '' && $this->isLocalhostUrl($this->baseUrl) && !$this->isLocalAppHost()) {
            $issues[] = 'Base URL uses localhost but this site is on live hosting. '
                . 'Deploy OpenWA on Render/Railway/VPS (see Admin → OpenWA → Live setup) and paste the public HTTPS URL here.';
        }

        if ($this->baseUrl !== '' && is_live_site_host() && !str_starts_with(strtolower($this->baseUrl), 'https://')) {
            $issues[] = 'On live sites use HTTPS for OpenWA Base URL (e.g. https://your-openwa.onrender.com).';
        }

        if ($this->baseUrl !== '' && self::isInvalidOpenWaBaseUrl($this->baseUrl)) {
            $issues[] = 'Base URL galat hai — yahan Findownn ya setup URL mat daalo. '
                . 'Sirf OpenWA cloud server URL (e.g. https://findownn-openwa.onrender.com), bina ?key= ke.';
        }

        return $issues;
    }

    /** Reject Findownn site URLs mistaken for OpenWA server URL */
    public static function isInvalidOpenWaBaseUrl(string $url): bool
    {
        $lower = strtolower($url);

        if (str_contains($lower, 'openwa-setup.php')
            || str_contains($lower, '?key=')
            || str_contains($lower, '/admin/public')
            || str_contains($lower, '/admin/openwa')
            || str_contains($lower, 'hostingersite.com')) {
            return true;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $appHost = strtolower($_SERVER['HTTP_HOST'] ?? '');

        return $appHost !== '' && $host !== '' && $host === $appHost;
    }

    public static function normalizeBaseUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $parts = parse_url($url);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return rtrim($url, '/');
        }

        $normalized = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $normalized .= ':' . $parts['port'];
        }

        return rtrim($normalized, '/');
    }

    public function isLocalhostUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array(strtolower((string) $host), ['localhost', '127.0.0.1', '::1'], true);
    }

    private function isLocalAppHost(): bool
    {
        return !is_live_site_host();
    }

    /** Resolve configured session id (UUID or name) */
    public function resolveSessionId(): string
    {
        if (!$this->isConfigured()) {
            return $this->sessionId;
        }

        try {
            $this->request('GET', "/api/sessions/{$this->sessionId}");
            return $this->sessionId;
        } catch (Exception) {
            // fall through — try matching by session name
        }

        try {
            $res      = $this->request('GET', '/api/sessions');
            $sessions = $res['body'] ?? [];
            if (!is_array($sessions)) {
                return $this->sessionId;
            }

            foreach ($sessions as $session) {
                if (!is_array($session)) {
                    continue;
                }
                $id   = (string) ($session['id'] ?? '');
                $name = (string) ($session['name'] ?? '');
                if ($id === $this->sessionId || $name === $this->sessionId) {
                    return $id !== '' ? $id : $this->sessionId;
                }
            }
        } catch (Exception) {
            // keep configured value
        }

        return $this->sessionId;
    }

    public function isSessionReady(): bool
    {
        $status = strtolower((string) ($this->getSessionStatus()['status'] ?? ''));

        return in_array($status, ['ready', 'connected', 'open', 'online', 'running'], true);
    }

    /** Create session if missing and attempt to start the WhatsApp engine */
    public function ensureSession(): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('OpenWA Base URL and API Key are required.');
        }

        $sessionId = $this->resolveSessionId();
        $exists    = false;

        try {
            $this->request('GET', "/api/sessions/{$sessionId}");
            $exists = true;
        } catch (Exception) {
            try {
                $res      = $this->request('GET', '/api/sessions');
                $sessions = $res['body'] ?? [];
                if (is_array($sessions)) {
                    foreach ($sessions as $session) {
                        if (!is_array($session)) {
                            continue;
                        }
                        $id   = (string) ($session['id'] ?? '');
                        $name = (string) ($session['name'] ?? '');
                        if ($id === $this->sessionId || $name === $this->sessionId) {
                            $sessionId = $id !== '' ? $id : $sessionId;
                            $exists    = true;
                            break;
                        }
                    }
                }
            } catch (Exception) {
                // fall through to create
            }
        }

        if (!$exists) {
            $created   = $this->createSession($this->sessionId);
            $sessionId = (string) ($created['id'] ?? $this->sessionId);
        }

        $status = strtolower((string) (($this->request('GET', "/api/sessions/{$sessionId}")['body']['status'] ?? '')));

        if (!$this->isReadyStatus($status)) {
            try {
                $this->request('POST', "/api/sessions/{$sessionId}/start");
            } catch (Exception) {
                // Already starting or waiting for QR — surface latest status below
            }
        }

        return $this->request('GET', "/api/sessions/{$sessionId}")['body'] ?? [];
    }

    private function isReadyStatus(string $status): bool
    {
        return in_array($status, ['ready', 'connected', 'open', 'online', 'running'], true);
    }

    public function sessionStatusHint(array $session): string
    {
        $status = strtolower((string) ($session['status'] ?? 'unknown'));

        return match ($status) {
            'qr', 'qr_ready', 'action_required', 'authenticating', 'initializing'
                => 'Scan the WhatsApp QR code in the OpenWA Web Dashboard, then send a test message again.',
            'disconnected', 'stopped'
                => 'Session is stopped. Click Test Connection again or start the session from the OpenWA dashboard.',
            default
                => 'Session status: ' . ($session['status'] ?? 'unknown') . '. Open the Web Dashboard link above if WhatsApp is not linked yet.',
        };
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getSwaggerUrl(): string
    {
        return $this->baseUrl . '/api/docs';
    }

    public function getDashboardUrl(): string
    {
        return preg_replace('#/api$#', '', $this->baseUrl) ?: $this->baseUrl;
    }

    /** All OpenWA features with live status */
    public function getFeatureMatrix(): array
    {
        $online = $this->isConfigured() && $this->healthCheck()['ok'];

        $features = [
            'core' => [
                ['name' => 'REST API',           'key' => 'rest_api',        'desc' => 'Full WhatsApp API via HTTP endpoints'],
                ['name' => 'Multi-Session',      'key' => 'multi_session',   'desc' => 'Manage multiple WhatsApp accounts'],
                ['name' => 'Webhooks',           'key' => 'webhooks',        'desc' => 'Real-time events with HMAC signature'],
                ['name' => 'Web Dashboard',      'key' => 'web_dashboard',   'desc' => 'Visual management interface'],
                ['name' => 'API Key Auth',       'key' => 'api_key_auth',    'desc' => 'Secure API authentication'],
                ['name' => 'Swagger Docs',       'key' => 'swagger_docs',    'desc' => 'Interactive API documentation'],
            ],
            'messaging' => [
                ['name' => 'Text Messages',      'key' => 'text_messages',   'desc' => 'Send and receive text messages'],
                ['name' => 'Media Messages',     'key' => 'media_messages',  'desc' => 'Images, videos, documents, audio'],
                ['name' => 'Message Reactions',  'key' => 'reactions',       'desc' => 'React to messages with emojis'],
                ['name' => 'Message Editing',    'key' => 'message_edit',    'desc' => 'Edit sent messages'],
                ['name' => 'Bulk Messaging',     'key' => 'bulk_messaging',  'desc' => 'Send to multiple recipients'],
                ['name' => 'Message Status',     'key' => 'message_status',  'desc' => 'Delivery and read receipts'],
            ],
            'advanced' => [
                ['name' => 'Groups API',         'key' => 'groups',          'desc' => 'Create and manage WhatsApp groups'],
                ['name' => 'Profile Management', 'key' => 'profile',         'desc' => 'Display name, about, profile picture'],
                ['name' => 'Call Handling',      'key' => 'calls',           'desc' => 'Auto-reject incoming calls'],
                ['name' => 'Channels/Newsletter','key' => 'channels',        'desc' => 'WhatsApp Channels support'],
                ['name' => 'Labels Management',  'key' => 'labels',          'desc' => 'Organize chats with labels'],
                ['name' => 'Proxy Support',      'key' => 'proxy',           'desc' => 'Per-session proxy configuration'],
                ['name' => 'Rate Limiting',      'key' => 'rate_limit',      'desc' => 'Configurable request limits'],
                ['name' => 'CIDR Whitelisting',  'key' => 'cidr',            'desc' => 'IP-based access control'],
                ['name' => 'Audit Logging',      'key' => 'audit_log',       'desc' => 'Track API key and message activity'],
            ],
            'infrastructure' => [
                ['name' => 'SQLite',             'key' => 'sqlite',          'desc' => 'Zero-config embedded database'],
                ['name' => 'PostgreSQL',         'key' => 'postgresql',      'desc' => 'Production-grade database'],
                ['name' => 'Redis Cache',        'key' => 'redis',           'desc' => 'Optional performance caching'],
                ['name' => 'S3/MinIO Storage',   'key' => 's3',              'desc' => 'Media backup and migration'],
                ['name' => 'Docker',             'key' => 'docker',          'desc' => 'One-command deployment'],
                ['name' => 'Health Checks',      'key' => 'health',          'desc' => 'Kubernetes-ready probes'],
                ['name' => 'Data Migration',     'key' => 'migration',       'desc' => 'Export/import between backends'],
            ],
        ];

        foreach ($features as $group => &$items) {
            foreach ($items as &$item) {
                $item['status'] = $online ? 'active' : ($this->isConfigured() ? 'offline' : 'not_configured');
            }
        }

        return $features;
    }

    public function healthCheck(): array
    {
        $diagnostics = $this->getConfigDiagnostics();
        if ($diagnostics !== []) {
            return ['ok' => false, 'error' => $diagnostics[0], 'issues' => $diagnostics];
        }

        $paths      = ['/api/health', '/api/health/live', '/health'];
        $lastError  = 'OpenWA server unreachable';

        foreach ($paths as $path) {
            try {
                $res = $this->request('GET', $path);
                if ($res['http_code'] < 500) {
                    return ['ok' => true, 'endpoint' => $path, 'data' => $res['body']];
                }
                $lastError = "OpenWA returned HTTP {$res['http_code']} on {$path}";
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        if ($this->isLocalhostUrl($this->baseUrl) && !$this->isLocalAppHost()) {
            $lastError .= ' — localhost only works when PHP runs on the same machine as OpenWA (local XAMPP).';
        } else {
            $lastError .= ' — is the OpenWA Docker/server running? Check: ' . $this->baseUrl . '/api/health';
        }

        return ['ok' => false, 'error' => $lastError];
    }

    public function getSessions(): array
    {
        $res = $this->request('GET', '/api/sessions');
        return $res['body'] ?? [];
    }

    public function createSession(string $name = 'findownn'): array
    {
        return $this->request('POST', '/api/sessions', ['name' => $name])['body'] ?? [];
    }

    public function getSessionStatus(): array
    {
        try {
            $sessionId = $this->resolveSessionId();
            $res       = $this->request('GET', "/api/sessions/{$sessionId}");
            return $res['body'] ?? [];
        } catch (Exception $e) {
            return ['status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    /** Send text message via OpenWA */
    public function sendText(string $phone, string $text): array
    {
        $chatId    = $this->toChatId($phone);
        $sessionId = $this->resolveSessionId();

        $res = $this->request('POST', "/api/sessions/{$sessionId}/messages/send-text", [
            'chatId' => $chatId,
            'text'   => $text,
        ]);

        $body = $res['body'] ?? [];

        return [
            'message_id' => $body['messageId'] ?? $body['id'] ?? null,
            'timestamp'  => $body['timestamp'] ?? null,
        ];
    }

    /** Send media (image/document/video/audio) */
    public function sendMedia(string $phone, string $mediaUrl, string $caption = '', string $type = 'image'): array
    {
        $chatId = $this->toChatId($phone);
        $endpoint = match ($type) {
            'video'    => 'send-video',
            'audio'    => 'send-audio',
            'document' => 'send-document',
            default    => 'send-image',
        };

        $payload = ['chatId' => $chatId, 'url' => $mediaUrl];
        if ($caption !== '') {
            $payload['caption'] = $caption;
        }

        $sessionId = $this->resolveSessionId();
        $res = $this->request('POST', "/api/sessions/{$sessionId}/messages/{$endpoint}", $payload);

        return ['message_id' => ($res['body']['messageId'] ?? null)];
    }

    /** Bulk text messaging */
    public function sendBulk(array $phones, string $text): array
    {
        $chatIds = array_map(fn ($p) => $this->toChatId($p), $phones);

        $sessionId = $this->resolveSessionId();
        $res = $this->request('POST', "/api/sessions/{$sessionId}/messages/send-bulk", [
            'messages' => array_map(fn ($id) => ['chatId' => $id, 'text' => $text], $chatIds),
        ]);

        return $res['body'] ?? [];
    }

    /** Register webhook on OpenWA session */
    public function setupWebhook(string $url, array $events = [], ?string $secret = null): array
    {
        $events = $events ?: [
            'message.received',
            'message.status',
            'session.status',
        ];

        $payload = [
            'url'    => $url,
            'events' => $events,
        ];

        if ($secret) {
            $payload['secret'] = $secret;
        }

        $sessionId = $this->resolveSessionId();
        $res = $this->request('POST', "/api/sessions/{$sessionId}/webhooks", $payload);

        return $res['body'] ?? [];
    }

    public function listWebhooks(): array
    {
        try {
            $sessionId = $this->resolveSessionId();
            $res = $this->request('GET', "/api/sessions/{$sessionId}/webhooks");
            return $res['body'] ?? [];
        } catch (Exception) {
            return [];
        }
    }

    private function toChatId(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        }
        return $digits . '@c.us';
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        $url = $this->baseUrl . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
            ],
        ]);

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("OpenWA connection error: {$error}");
        }

        $decoded = json_decode($response ?: 'null', true);

        if ($httpCode >= 400) {
            $msg = is_array($decoded)
                ? ($decoded['message'] ?? $decoded['error'] ?? json_encode($decoded))
                : ($response ?: 'Unknown error');
            throw new Exception("OpenWA API error ({$httpCode}): {$msg}");
        }

        return ['http_code' => $httpCode, 'body' => $decoded];
    }
}
