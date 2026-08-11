<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Session;

class ActivityLog extends Model
{
    protected string $table    = 'activity_logs';
    protected array  $fillable = [
        'user_id', 'description', 'type', 'subject_type', 'subject_id',
        'ip_address', 'created_at',
    ];

    public static function record(string $description, string $type = 'info', string $subjectType = '', int $subjectId = 0): void
    {
        $instance = new static();
        $userId   = Session::get('user')['id'] ?? null;

        $instance->db->execute(
            "INSERT INTO activity_logs
             (user_id, description, type, subject_type, subject_id, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $userId, $description, $type, $subjectType, $subjectId,
                $_SERVER['REMOTE_ADDR'] ?? '', now(),
            ]
        );
    }

    public function getRecent(int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, u.name AS user_name, u.avatar
             FROM activity_logs a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function getRecentForUser(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM activity_logs
             WHERE user_id = ?
             ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
