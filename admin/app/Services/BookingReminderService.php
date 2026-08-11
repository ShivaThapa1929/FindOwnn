<?php

namespace App\Services;

use App\Core\Database;

class BookingReminderService
{
    private Database $db;
    private WhatsAppService $whatsapp;

    public function __construct()
    {
        $this->db       = Database::getInstance();
        $this->whatsapp = new WhatsAppService();
    }

    /**
     * Send a booking reminder for a specific booking ID.
     */
    public function sendForBooking(int $bookingId): array
    {
        $booking = $this->getBookingPayload($bookingId);
        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found'];
        }

        if (!in_array($booking['status'], ['confirmed', 'pending'], true)) {
            return ['success' => false, 'error' => 'Reminders can only be sent for active bookings'];
        }

        $phone = $this->resolvePhone($booking);
        if (!$phone) {
            return ['success' => false, 'error' => 'No phone number on file for this player'];
        }

        if (!$this->whatsapp->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp is not configured. Set it up in Settings.'];
        }

        $booking['user_phone'] = $phone;
        $result = $this->whatsapp->sendBookingReminder($booking);

        if ($result['success']) {
            $this->markReminderSent($bookingId, (int) $booking['user_id']);
        }

        return $result;
    }

    /**
     * Send automated reminders for upcoming bookings (cron).
     */
    public function sendUpcomingReminders(): array
    {
        if (!$this->whatsapp->isConfigured()) {
            return ['sent' => 0, 'skipped' => 0, 'errors' => ['WhatsApp not configured']];
        }

        $hoursBefore = (int) ($this->getSetting('reminder_hours_before') ?: 24);
        $enabled     = $this->getSetting('send_reminder') !== '0';

        if (!$enabled) {
            return ['sent' => 0, 'skipped' => 0, 'errors' => ['Reminders disabled in settings']];
        }

        $bookings = $this->getBookingsDueForReminder($hoursBefore);
        $sent = 0;
        $skipped = 0;
        $errors = [];

        foreach ($bookings as $row) {
            $phone = $this->resolvePhone($row);
            if (!$phone) {
                $skipped++;
                continue;
            }

            $row['user_phone'] = $phone;
            $result = $this->whatsapp->sendBookingReminder($row);

            if ($result['success']) {
                $this->markReminderSent((int) $row['id'], (int) $row['user_id']);
                $sent++;
            } else {
                $errors[] = "Booking #{$row['booking_reference']}: " . ($result['error'] ?? 'Failed');
            }
        }

        return compact('sent', 'skipped', 'errors');
    }

    public function getBookingsDueForReminder(int $hoursBefore): array
    {
        $hasColumn = $this->hasReminderColumn();

        $reminderClause = $hasColumn
            ? 'AND (b.reminder_sent_at IS NULL)'
            : '';

        return $this->db->fetchAll(
            "SELECT b.*,
                    v.name AS venue_name, v.address AS venue_address,
                    s.name AS sport_name,
                    u.name AS user_name, u.phone AS user_phone,
                    u.whatsapp_number, u.whatsapp_opt_in
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             LEFT JOIN sports s ON b.sport_id = s.id
             JOIN users u ON b.user_id = u.id
             WHERE b.status IN ('confirmed', 'pending')
               AND b.booking_date >= CURDATE()
               AND CONCAT(b.booking_date, ' ', b.start_time)
                   BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? HOUR)
               {$reminderClause}
             ORDER BY b.booking_date ASC, b.start_time ASC",
            [$hoursBefore]
        );
    }

    private function getBookingPayload(int $bookingId): array|false
    {
        return $this->db->fetch(
            "SELECT b.*,
                    v.name AS venue_name, v.address AS venue_address,
                    s.name AS sport_name,
                    u.name AS user_name, u.email AS user_email,
                    u.phone AS user_phone, u.whatsapp_number, u.whatsapp_opt_in
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             LEFT JOIN sports s ON b.sport_id = s.id
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = ?",
            [$bookingId]
        );
    }

    private function resolvePhone(array $row): ?string
    {
        if (isset($row['whatsapp_opt_in']) && (int) $row['whatsapp_opt_in'] === 0) {
            return null;
        }

        $phone = trim($row['whatsapp_number'] ?? '') ?: trim($row['user_phone'] ?? '') ?: trim($row['phone'] ?? '');
        return $phone !== '' ? $phone : null;
    }

    private function markReminderSent(int $bookingId, int $userId): void
    {
        if ($this->hasReminderColumn()) {
            $this->db->execute(
                'UPDATE bookings SET reminder_sent_at = NOW() WHERE id = ?',
                [$bookingId]
            );
        }

        $this->db->execute(
            'UPDATE users SET last_whatsapp_sent = NOW() WHERE id = ?',
            [$userId]
        );
    }

    private function hasReminderColumn(): bool
    {
        static $checked = null;
        if ($checked !== null) {
            return $checked;
        }

        try {
            $col = $this->db->fetch(
                "SHOW COLUMNS FROM bookings LIKE 'reminder_sent_at'"
            );
            $checked = !empty($col);
        } catch (\Throwable) {
            $checked = false;
        }

        return $checked;
    }

    private function getSetting(string $key): ?string
    {
        $row = $this->db->fetch(
            "SELECT value FROM settings WHERE `group` = 'whatsapp' AND `key` = ? LIMIT 1",
            [$key]
        );

        return $row['value'] ?? null;
    }
}
