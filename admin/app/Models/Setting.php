<?php

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table    = 'settings';
    protected array  $fillable = ['key', 'value', 'group', 'type', 'label', 'updated_at'];

    private static array $cache = [];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $instance = new static();
        $row = $instance->db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
        $value = $row ? $row['value'] : $default;
        self::$cache[$key] = $value;
        return $value;
    }

    public static function setValue(string $key, mixed $value): void
    {
        $instance = new static();
        $exists   = $instance->db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($exists) {
            $instance->db->execute("UPDATE settings SET value = ?, updated_at = ? WHERE `key` = ?", [$value, now(), $key]);
        } else {
            $instance->db->execute("INSERT INTO settings (`key`, value, created_at, updated_at) VALUES (?, ?, ?, ?)", [$key, $value, now(), now()]);
        }
        self::$cache[$key] = $value;
    }

    public function getByGroup(string $group): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM settings WHERE `group` = ? ORDER BY id ASC",
            [$group]
        );
    }
}
