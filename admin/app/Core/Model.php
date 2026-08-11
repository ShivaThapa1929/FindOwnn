<?php

namespace App\Core;

/**
 * Base Model — Active-Record style helper over PDO.
 * All models extend this class to get CRUD helpers automatically.
 */
abstract class Model
{
    protected Database $db;
    protected string   $table  = '';
    protected string   $primaryKey = 'id';
    protected array    $fillable   = [];
    protected array    $hidden     = ['password'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ----------------------------------------------------------------
    // Finders
    // ----------------------------------------------------------------

    public function find(int|string $id): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1",
            [$id]
        );
    }

    public function findOrFail(int|string $id): array
    {
        $record = $this->find($id);
        if (!$record) {
            throw new \RuntimeException("Record [{$id}] not found in [{$this->table}].");
        }
        return $record;
    }

    public function findBy(string $column, mixed $value): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1",
            [$value]
        );
    }

    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}"
        );
    }

    public function where(string $column, mixed $value, string $operator = '='): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?",
            [$value]
        );
    }

    public function count(string $where = '1', array $params = []): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$where}",
            $params
        );
    }

    // ----------------------------------------------------------------
    // Writes
    // ----------------------------------------------------------------

    public function create(array $data): string
    {
        $data = $this->filterFillable($data);
        $data['created_at'] = $data['created_at'] ?? now();
        $data['updated_at'] = $data['updated_at'] ?? now();

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return $this->db->insert(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public function update(int|string $id, array $data): int
    {
        $data = $this->filterFillable($data);
        $data['updated_at'] = now();

        $set = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;

        return $this->db->execute(
            "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?",
            $values
        );
    }

    public function delete(int|string $id): int
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    /** Soft delete — sets deleted_at timestamp */
    public function softDelete(int|string $id): int
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET deleted_at = ?, updated_at = ? WHERE {$this->primaryKey} = ?",
            [now(), now(), $id]
        );
    }

    // ----------------------------------------------------------------
    // Pagination
    // ----------------------------------------------------------------

    public function paginate(int $page, int $perPage = 15, string $where = '1', array $params = [], string $orderBy = 'id DESC'): array
    {
        $total  = $this->count($where, $params);
        $offset = ($page - 1) * $perPage;
        $pages  = (int) ceil($total / $perPage);

        $data = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return compact('data', 'total', 'page', 'perPage', 'pages');
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function filterFillable(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function hideFields(array $record): array
    {
        foreach ($this->hidden as $field) {
            unset($record[$field]);
        }
        return $record;
    }
}
