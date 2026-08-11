<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database — Singleton PDO wrapper.
 * Provides safe query execution with prepared statements.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $host   = Config::get('DB_HOST',     'localhost');
        $port   = Config::get('DB_PORT',     '3306');
        $db     = Config::get('DB_DATABASE', 'findownn_admin');
        $user   = Config::get('DB_USERNAME', 'root');
        $pass   = Config::get('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            Logger::error('DB Connection Failed: ' . $e->getMessage());
            $hint = 'Database connection failed. Check DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in admin/.env';
            if (Config::get('APP_DEBUG') === 'true') {
                $hint .= ' — ' . $e->getMessage();
            }
            throw new \RuntimeException($hint);
        }
    }

    public static function getInstance(): static
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /** Execute a prepared statement and return the statement */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error("Query failed: {$sql} | " . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    public function insert(string $sql, array $params = []): string
    {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }

    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function beginTransaction(): void  { $this->pdo->beginTransaction(); }
    public function commit(): void            { $this->pdo->commit(); }
    public function rollback(): void          { $this->pdo->rollBack(); }

    /** Run raw SQL (migrations only — never pass user input here) */
    public function rawExec(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    // Prevent cloning / unserialization
    private function __clone() {}
    public function __wakeup() { throw new \RuntimeException('Cannot unserialize singleton.'); }
}
