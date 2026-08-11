<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;

class SportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all sports
     */
    public function index(Request $request): void
    {
        $sports = $this->db->fetchAll(
            "SELECT s.*,
                (SELECT COUNT(DISTINCT vs.venue_id) FROM venue_sports vs WHERE vs.sport_id = s.id) as total_venues,
                (SELECT COUNT(*) FROM courts c WHERE c.sport_id = s.id AND c.deleted_at IS NULL) as total_courts
             FROM sports s
             ORDER BY s.sort_order ASC, s.name ASC"
        );

        $this->render('sports.index', [
            'title'   => 'Manage Sports',
            'sports'  => $sports,
            'success' => Session::getFlash('success'),
            'error'   => Session::getFlash('error'),
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request): void
    {
        $this->render('sports.create', [
            'title' => 'Add New Sport',
            'error' => Session::getFlash('error'),
        ]);
    }

    /**
     * Store new sport
     */
    public function store(Request $request): void
    {
        $data = $request->only(['name', 'slug', 'description', 'icon', 'color', 'sort_order', 'is_active', 'is_featured']);

        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Sport name is required.';
        }
        if (empty(trim($data['slug'] ?? ''))) {
            $data['slug'] = strtolower(str_replace([' ', '_'], '-', $data['name'] ?? ''));
        }

        // Check unique slug
        $existing = $this->db->fetch("SELECT id FROM sports WHERE slug = ?", [$data['slug']]);
        if ($existing) {
            $errors[] = 'A sport with this slug already exists.';
        }

        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            redirect(url('/sports/create'));
        }

        $this->db->query(
            "INSERT INTO sports (name, slug, description, icon, color, sort_order, is_active, is_featured, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                trim($data['name']),
                trim($data['slug']),
                trim($data['description'] ?? ''),
                trim($data['icon'] ?? 'bi-trophy'),
                trim($data['color'] ?? '#22c55e'),
                (int) ($data['sort_order'] ?? 99),
                isset($data['is_active']) ? 1 : 0,
                isset($data['is_featured']) ? 1 : 0,
            ]
        );

        Session::flash('success', "Sport '{$data['name']}' created successfully.");
        redirect(url('/sports'));
    }

    /**
     * Show edit form
     */
    public function edit(Request $request): void
    {
        $id    = (int) $request->param('id');
        $sport = $this->db->fetch("SELECT * FROM sports WHERE id = ?", [$id]);

        if (!$sport) {
            Session::flash('error', 'Sport not found.');
            redirect(url('/sports'));
        }

        $this->render('sports.edit', [
            'title' => 'Edit Sport: ' . e($sport['name']),
            'sport' => $sport,
            'error' => Session::getFlash('error'),
        ]);
    }

    /**
     * Update sport
     */
    public function update(Request $request): void
    {
        $id    = (int) $request->param('id');
        $sport = $this->db->fetch("SELECT * FROM sports WHERE id = ?", [$id]);

        if (!$sport) {
            Session::flash('error', 'Sport not found.');
            redirect(url('/sports'));
        }

        $data = $request->only(['name', 'slug', 'description', 'icon', 'color', 'sort_order', 'is_active', 'is_featured']);

        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Sport name is required.';
        }
        if (empty(trim($data['slug'] ?? ''))) {
            $data['slug'] = strtolower(str_replace([' ', '_'], '-', $data['name'] ?? ''));
        }

        // Check unique slug excluding current
        $existing = $this->db->fetch("SELECT id FROM sports WHERE slug = ? AND id != ?", [$data['slug'], $id]);
        if ($existing) {
            $errors[] = 'A sport with this slug already exists.';
        }

        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            redirect(url("/sports/{$id}/edit"));
        }

        $this->db->query(
            "UPDATE sports SET name=?, slug=?, description=?, icon=?, color=?, sort_order=?, is_active=?, is_featured=?, updated_at=NOW() WHERE id=?",
            [
                trim($data['name']),
                trim($data['slug']),
                trim($data['description'] ?? ''),
                trim($data['icon'] ?? 'bi-trophy'),
                trim($data['color'] ?? '#22c55e'),
                (int) ($data['sort_order'] ?? 99),
                isset($data['is_active']) ? 1 : 0,
                isset($data['is_featured']) ? 1 : 0,
                $id,
            ]
        );

        Session::flash('success', "Sport '{$data['name']}' updated successfully.");
        redirect(url('/sports'));
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(Request $request): void
    {
        $id    = (int) $request->param('id');
        $sport = $this->db->fetch("SELECT * FROM sports WHERE id = ?", [$id]);

        if (!$sport) {
            Session::flash('error', 'Sport not found.');
            redirect(url('/sports'));
        }

        $newStatus = $sport['is_active'] ? 0 : 1;
        $this->db->query("UPDATE sports SET is_active=?, updated_at=NOW() WHERE id=?", [$newStatus, $id]);

        $label = $newStatus ? 'activated' : 'deactivated';
        Session::flash('success', "Sport '{$sport['name']}' {$label}.");
        redirect(url('/sports'));
    }

    /**
     * Delete sport
     */
    public function destroy(Request $request): void
    {
        $id    = (int) $request->param('id');
        $sport = $this->db->fetch("SELECT * FROM sports WHERE id = ?", [$id]);

        if (!$sport) {
            Session::flash('error', 'Sport not found.');
            redirect(url('/sports'));
        }

        // Check if sport is in use
        $venueCount = $this->db->fetchColumn("SELECT COUNT(*) FROM venue_sports WHERE sport_id = ?", [$id]);
        if ($venueCount > 0) {
            Session::flash('error', "Cannot delete '{$sport['name']}' — it is assigned to {$venueCount} venue(s). Remove it from venues first.");
            redirect(url('/sports'));
        }

        $this->db->query("DELETE FROM sports WHERE id=?", [$id]);
        Session::flash('success', "Sport '{$sport['name']}' deleted.");
        redirect(url('/sports'));
    }
}
