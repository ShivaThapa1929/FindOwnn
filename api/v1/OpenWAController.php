<?php

namespace Api\V1;

use App\Core\Database;

require_once __DIR__ . '/ApiController.php';

class OpenWAController extends ApiController
{
    public static function handle(string $method, ?string $action, array $body): array
    {
        if ($method === 'POST' && ($action === 'webhook' || $action === null)) {
            return self::receiveWebhook($body);
        }

        return self::error('Method not allowed', 405);
    }

    private static function receiveWebhook(array $body): array
    {
        $db = Database::getInstance();

        $rawPayload = file_get_contents('php://input') ?: json_encode($body);
        $signature  = $_SERVER['HTTP_X_OPENWA_SIGNATURE']
            ?? $_SERVER['HTTP_X_HUB_SIGNATURE_256']
            ?? null;

        $verified = 0;
        $secret   = self::getWebhookSecret($db);

        if ($secret && $signature) {
            $expected = 'sha256=' . hash_hmac('sha256', $rawPayload, $secret);
            $verified = hash_equals($expected, $signature) ? 1 : 0;
        }

        $eventType = $body['event'] ?? $body['type'] ?? 'unknown';

        try {
            $db->insert(
                "INSERT INTO webhook_logs (source, event_type, payload, signature, is_verified, processed, created_at)
                 VALUES ('openwa', ?, ?, ?, ?, 0, NOW())",
                [$eventType, $rawPayload, $signature, $verified]
            );
        } catch (\Throwable) {
            // webhook_logs table may not exist yet
        }

        // Handle message status updates
        if (str_contains($eventType, 'message.status') && !empty($body['messageId'])) {
            self::updateMessageStatus($db, $body);
        }

        return self::success(['received' => true, 'event' => $eventType]);
    }

    private static function getWebhookSecret(Database $db): ?string
    {
        $row = $db->fetch(
            "SELECT value FROM settings WHERE `group` = 'whatsapp' AND `key` = 'openwa_webhook_secret' LIMIT 1"
        );
        return $row['value'] ?? null;
    }

    private static function updateMessageStatus(Database $db, array $body): void
    {
        $providerId = $body['messageId'] ?? null;
        $status     = $body['status'] ?? null;

        if (!$providerId || !$status) {
            return;
        }

        $map = [
            'sent'      => 'sent',
            'delivered' => 'delivered',
            'read'      => 'read',
            'failed'    => 'failed',
        ];

        $dbStatus = $map[$status] ?? null;
        if (!$dbStatus) {
            return;
        }

        try {
            $db->execute(
                "UPDATE whatsapp_messages SET status = ?, updated_at = NOW() WHERE provider_message_id = ?",
                [$dbStatus, $providerId]
            );
        } catch (\Throwable) {
            // ignore
        }
    }
}
