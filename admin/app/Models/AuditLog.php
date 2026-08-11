<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Session;

class AuditLog extends Model
{
    protected string $table    = 'audit_logs';
    protected array  $fillable = [
        'user_id', 'action', 'model', 'model_id',
        'old_values', 'new_values', 'ip_address', 'user_agent',
        'created_at',
    ];

    public static function log(
        string $action,
        string $model,
        int|string $modelId = 0,
        array $oldValues = [],
        array $newValues = []
    ): void {
        $instance = new static();
        $userId   = Session::get('user')['id'] ?? null;

        $instance->db->execute(
            "INSERT INTO audit_logs
             (user_id, action, model, model_id, old_values, new_values, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                $model,
                $modelId,
                json_encode($oldValues),
                json_encode($newValues),
                $_SERVER['REMOTE_ADDR']  ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                now(),
            ]
        );
    }

    public function getWithUser(int $page = 1, int $perPage = 30): array
    {
        $total  = $this->count();
        $offset = ($page - 1) * $perPage;
        $pages  = (int) ceil($total / $perPage);

        $data = $this->db->fetchAll(
            "SELECT a.*, u.name AS user_name, u.role AS user_role
             FROM audit_logs a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );

        return compact('data', 'total', 'page', 'perPage', 'pages');
    }
}
