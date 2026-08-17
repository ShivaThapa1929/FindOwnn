<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;

class ContactMessageController extends Controller
{
    public function index(Request $request): void
    {
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
        $id = (int) $request->param('id');
        $this->db->execute('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
        Session::flash('success', 'Message marked as read.');
        $this->redirect(url('/contact-messages'));
    }
}
