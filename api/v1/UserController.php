<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

class UserController extends ApiController
{
    public static function handle($method, $action, $query, $body)
    {
        self::requireAuth();
        
        if ($action === 'profile') {
            if ($method === 'GET') {
                return self::getProfile();
            } elseif ($method === 'PUT' || $method === 'POST') {
                return self::updateProfile($body);
            }
        }
        
        if ($action === 'change-password' && $method === 'POST') {
            return self::changePassword($body);
        }
        
        if ($action === 'bookings' && $method === 'GET') {
            return self::getBookings($query);
        }
        
        if ($action === 'stats' && $method === 'GET') {
            return self::getStats();
        }
        
        // Default — return profile
        if (!$action && $method === 'GET') {
            return self::getProfile();
        }
        
        return self::error('Invalid action: ' . $action, 404);
    }
    
    /**
     * Get user profile
     */
    private static function getProfile()
    {
        $user = self::$db->fetch(
            "SELECT id, name, email, phone, whatsapp_number, whatsapp_opt_in, avatar, role, status, created_at
             FROM users
             WHERE id = ?",
            [self::$user['id']]
        );
        
        // Get statistics
        $stats = self::$db->fetch(
            "SELECT 
             COUNT(*) as total_bookings,
             SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_spent,
             COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as upcoming_bookings
             FROM bookings
             WHERE user_id = ?",
            [self::$user['id']]
        );
        
        return self::success([
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'whatsapp_number' => $user['whatsapp_number'],
            'whatsapp_opt_in' => (bool) $user['whatsapp_opt_in'],
            'profile_image' => $user['avatar'],
            'role' => $user['role'],
            'status' => $user['status'],
            'total_bookings' => (int) $stats['total_bookings'],
            'total_spent' => (int) $stats['total_spent'],
            'upcoming_bookings' => (int) $stats['upcoming_bookings'],
            'created_at' => $user['created_at']
        ]);
    }
    
    /**
     * Update user profile
     */
    private static function updateProfile($data)
    {
        $updateData = [];
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }
        
        if (isset($data['whatsapp_number'])) {
            $updateData['whatsapp_number'] = $data['whatsapp_number'];
        }
        
        if (isset($data['whatsapp_opt_in'])) {
            $updateData['whatsapp_opt_in'] = $data['whatsapp_opt_in'] ? 1 : 0;
        }
        
        if (empty($updateData)) {
            return self::error('No fields to update', 400);
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        self::$db->update('users', self::$user['id'], $updateData);
        
        return self::success([
            'message' => 'Profile updated successfully'
        ]);
    }
    
    /**
     * Change password
     */
    private static function changePassword($data)
    {
        if (empty($data['current_password']) || empty($data['new_password'])) {
            return self::error('Current password and new password required', 400);
        }
        
        // Verify current password
        $user = self::$db->fetch(
            "SELECT password FROM users WHERE id = ?",
            [self::$user['id']]
        );
        
        if (!password_verify($data['current_password'], $user['password'])) {
            return self::error('Current password is incorrect', 400, 'AUTH_002');
        }
        
        // Update password
        self::$db->update('users', self::$user['id'], [
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return self::success([
            'message' => 'Password changed successfully'
        ]);
    }
    
    /**
     * Get user bookings (same as BookingController but here for convenience)
     */
    private static function getBookings($query)
    {
        require_once __DIR__ . '/BookingController.php';
        return BookingController::handle('GET', null, null, $query, null);
    }
    
    /**
     * Get user statistics
     */
    private static function getStats()
    {
        $stats = self::$db->fetch(
            "SELECT 
             COUNT(*) as total_bookings,
             COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_bookings,
             COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as upcoming_bookings,
             COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
             SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_spent,
             AVG(CASE WHEN status = 'completed' THEN amount END) as avg_booking_amount
             FROM bookings
             WHERE user_id = ?",
            [self::$user['id']]
        );
        
        // Get favorite sport
        $favoriteSport = self::$db->fetch(
            "SELECT s.name, COUNT(*) as booking_count
             FROM bookings b
             JOIN courts c ON b.court_id = c.id
             JOIN sports s ON c.sport_id = s.id
             WHERE b.user_id = ?
             GROUP BY s.id
             ORDER BY booking_count DESC
             LIMIT 1",
            [self::$user['id']]
        );
        
        return self::success([
            'total_bookings' => (int) $stats['total_bookings'],
            'completed_bookings' => (int) $stats['completed_bookings'],
            'upcoming_bookings' => (int) $stats['upcoming_bookings'],
            'cancelled_bookings' => (int) $stats['cancelled_bookings'],
            'total_spent' => (int) $stats['total_spent'],
            'avg_booking_amount' => (int) $stats['avg_booking_amount'],
            'favorite_sport' => $favoriteSport ? $favoriteSport['name'] : null
        ]);
    }
}
