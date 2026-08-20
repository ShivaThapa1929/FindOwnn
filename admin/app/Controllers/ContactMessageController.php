<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;

class ContactMessageController extends Controller
{
    private function ensureTable(): void
    {
        try {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS contact_messages (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(120) NOT NULL,
                    email VARCHAR(190) NOT NULL,
                    phone VARCHAR(20) NULL,
                    subject VARCHAR(120) NOT NULL,
                    message TEXT NOT NULL,
                    ip_address VARCHAR(45) NULL,
                    is_read TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_contact_created (created_at),
                    INDEX idx_contact_read (is_read)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Throwable $e) {}
    }

    public function index(Request $request): void
    {
        $this->ensureTable();

        $page    = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $total = (int) $this->db->fetchColumn('SELECT COUNT(*) FROM contact_messages');
        $rows  = $this->db->fetchAll(
            'SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );

        $this->render('contact-messages.index', [
            'title'   => 'Contact Messages',
            'rows'    => $rows,
            'page'    => $page,
            'total'   => $total,
            'pages'   => max(1, (int) ceil($total / $perPage)),
            'success' => Session::getFlash('success'),
        ]);
    }

    public function markRead(Request $request): void
    {
        $this->ensureTable();

        $id = (int) $request->param('id');
        $this->db->execute('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
        Session::flash('success', 'Message marked as read.');
        $this->redirect(url('/contact-messages'));
    }
}
