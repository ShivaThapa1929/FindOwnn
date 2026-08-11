<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\Venue;
use App\Models\Subscription;
use App\Models\AuditLog;

class ReportController extends Controller
{
    public function index(Request $request): void
    {
        $bookModel  = new Booking();
        $subModel   = new Subscription();
        $venueModel = new Venue();
        $userModel  = new User();

        $monthlyRev  = $bookModel->getMonthlyRevenue(12);
        $bookStats   = $bookModel->getStats();
        $subStats    = $subModel->getStats();
        $venueStats  = $venueModel->getStats();

        // Revenue by sport type
        $revenueByType = $this->db->fetchAll(
            "SELECT s.name AS type, COUNT(b.id) AS bookings,
                    SUM(CASE WHEN b.payment_status = 'paid' THEN b.amount ELSE 0 END) AS revenue
             FROM bookings b
             LEFT JOIN sports s ON b.sport_id = s.id
             GROUP BY s.id, s.name
             ORDER BY revenue DESC"
        );

        // User registrations by month (last 6)
        $userGrowth = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
             FROM users WHERE deleted_at IS NULL
             AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC"
        );

        $this->render('reports.index', [
            'title'         => 'Analytics & Reports',
            'monthlyRev'    => $monthlyRev,
            'bookStats'     => $bookStats,
            'subStats'      => $subStats,
            'venueStats'    => $venueStats,
            'revenueByType' => $revenueByType,
            'userGrowth'    => $userGrowth,
        ]);
    }

    public function auditLogs(Request $request): void
    {
        $page  = (int) $request->query('page', 1);
        $logModel = new AuditLog();
        $result   = $logModel->getWithUser($page);

        $this->render('reports.audit-logs', [
            'title'  => 'Audit Logs',
            'result' => $result,
        ]);
    }

    public function activityLogs(Request $request): void
    {
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $total = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM activity_logs");

        $logs = $this->db->fetchAll(
            "SELECT a.*, u.name AS user_name, u.role AS user_role, u.avatar
             FROM activity_logs a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $pages = $total > 0 ? ceil($total / $perPage) : 1;

        $this->render('reports.activity-logs', [
            'title'  => 'Activity Logs',
            'result' => [
                'data'  => $logs,
                'total' => $total,
                'page'  => $page,
                'pages' => $pages,
            ],
        ]);
    }
}
