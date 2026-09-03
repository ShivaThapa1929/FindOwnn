<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class ImageController extends Controller
{
    private $uploadsPath;

    public function __construct()
    {
        parent::__construct();
        $this->uploadsPath = __DIR__ . '/../../public/uploads';

        // Create upload directories if they don't exist
        if (!is_dir($this->uploadsPath)) {
            mkdir($this->uploadsPath, 0755, true);
        }
        if (!is_dir($this->uploadsPath . '/venues')) {
            mkdir($this->uploadsPath . '/venues', 0755, true);
        }
        if (!is_dir($this->uploadsPath . '/courts')) {
            mkdir($this->uploadsPath . '/courts', 0755, true);
        }
    }

    // â”€â”€ Venue Images â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Upload venue image (AJAX)
     */
    public function uploadVenueImage(Request $request): void
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->log('info', 'Venue image upload started', [
            'post' => $_POST,
            'files' => array_keys($_FILES),
            'user' => $this->user()['id'] ?? 'none'
        ]);

        try {
            $venueId = $request->input('venue_id');
            $imageType = $request->input('image_type', 'gallery'); // featured or gallery

            if (!$venueId) {
                $this->log('error', 'Venue ID missing', ['post' => $_POST]);
                $this->json(['success' => false, 'message' => 'Venue ID required'], 400);
                return;
            }

            // Check ownership (venue owners can only upload to their venues)
            if ($this->user()['role'] === 'venue_owner') {
                $venue = $this->db->fetch("SELECT owner_id FROM venues WHERE id = ?", [$venueId]);
                if (!$venue || $venue['owner_id'] != $this->user()['id']) {
                    $this->log('error', 'Unauthorized venue access', ['venue_id' => $venueId, 'user_id' => $this->user()['id']]);
                    $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
                    return;
                }
            }

            if (empty($_FILES['image'])) {
                $this->log('error', 'No image file in request', ['files' => $_FILES]);
                $this->json(['success' => false, 'message' => 'No image file uploaded'], 400);
                return;
            }

            $file = $_FILES['image'];
            $this->log('info', 'Processing file upload', [
                'name' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size'],
                'error' => $file['error']
            ]);

            $uploadResult = $this->uploadImage($file, 'venues');

            if (!$uploadResult['success']) {
                $this->log('error', 'Upload failed', $uploadResult);
                $this->json($uploadResult, 400);
                return;
            }

            // Save to venue_images table
            $caption = $request->input('caption', '');
            $imageId = $this->db->insert(
                "INSERT INTO venue_images (venue_id, image_path, image_type, caption, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())",
                [$venueId, $uploadResult['path'], $imageType, $caption]
            );

            $this->log('info', 'Image uploaded successfully', ['image_id' => $imageId]);

            $this->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image' => [
                    'id' => $imageId,
                    'path' => $uploadResult['path'],
                    'url' => $uploadResult['url'],
                    'type' => $imageType,
                    'caption' => $caption
                ]
            ]);

        } catch (\Exception $e) {
            $this->log('error', 'Venue image upload exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete venue image
     */
    public function deleteVenueImage(Request $request): void
    {
        try {
            $imageId = $request->param('id');

            $image = $this->db->fetch("SELECT vi.*, v.owner_id FROM venue_images vi 
                                       JOIN venues v ON vi.venue_id = v.id 
                                       WHERE vi.id = ?", [$imageId]);

            if (!$image) {
                $this->json(['success' => false, 'message' => 'Image not found'], 404);
                return;
            }

            // Check ownership
            if ($this->user()['role'] === 'venue_owner' && $image['owner_id'] != $this->user()['id']) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
                return;
            }

            // Delete file
            $filePath = $this->uploadsPath . '/' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete record
            $this->db->execute("DELETE FROM venue_images WHERE id = ?", [$imageId]);

            $this->json(['success' => true, 'message' => 'Image deleted successfully']);

        } catch (\Exception $e) {
            $this->log('error', 'Venue image deletion failed', ['error' => $e->getMessage()]);
            $this->json(['success' => false, 'message' => 'Deletion failed'], 500);
        }
    }

    /**
     * Update venue image (caption, type, sort order)
     */
    public function updateVenueImage(Request $request): void
    {
        try {
            $imageId = $request->param('id');
            $caption = $request->input('caption', '');
            $imageType = $request->input('image_type', 'gallery');
            $sortOrder = $request->input('sort_order', 0);

            $image = $this->db->fetch("SELECT vi.*, v.owner_id FROM venue_images vi 
                                       JOIN venues v ON vi.venue_id = v.id 
                                       WHERE vi.id = ?", [$imageId]);

            if (!$image) {
                $this->json(['success' => false, 'message' => 'Image not found'], 404);
                return;
            }

            // Check ownership
            if ($this->user()['role'] === 'venue_owner' && $image['owner_id'] != $this->user()['id']) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
                return;
            }

            $this->db->execute(
                "UPDATE venue_images SET caption = ?, image_type = ?, sort_order = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$caption, $imageType, $sortOrder, $imageId]
            );

            $this->json(['success' => true, 'message' => 'Image updated successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Update failed'], 500);
        }
    }

    // â”€â”€ Court Images â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Upload court image (AJAX)
     */
    public function uploadCourtImage(Request $request): void
    {
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->log('info', 'Court image upload started', [
            'post' => $_POST,
            'files' => array_keys($_FILES),
            'user' => $this->user()['id'] ?? 'none'
        ]);

        try {
            $courtId = $request->input('court_id');
            $imageType = $request->input('image_type', 'gallery'); // featured or gallery

            if (!$courtId) {
                $this->log('error', 'Court ID missing', ['post' => $_POST]);
                $this->json(['success' => false, 'message' => 'Court ID required'], 400);
                return;
            }

            // Check ownership
            if ($this->user()['role'] === 'venue_owner') {
                $court = $this->db->fetch(
                    "SELECT c.*, v.owner_id FROM courts c 
                     JOIN venues v ON c.venue_id = v.id 
                     WHERE c.id = ?",
                    [$courtId]
                );
                if (!$court || $court['owner_id'] != $this->user()['id']) {
                    $this->log('error', 'Unauthorized court access', ['court_id' => $courtId, 'user_id' => $this->user()['id']]);
                    $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
                    return;
                }
            }

            if (empty($_FILES['image'])) {
                $this->log('error', 'No image file in request', ['files' => $_FILES]);
                $this->json(['success' => false, 'message' => 'No image file uploaded'], 400);
                return;
            }

            $file = $_FILES['image'];
            $this->log('info', 'Processing court file upload', [
                'name' => $file['name'],
                'type' => $file['type'],
                'size' => $file['size'],
                'error' => $file['error']
            ]);

            $uploadResult = $this->uploadImage($file, 'courts');

            if (!$uploadResult['success']) {
                $this->log('error', 'Court upload failed', $uploadResult);
                $this->json($uploadResult, 400);
                return;
            }

            // Save to court_images table
            $caption = $request->input('caption', '');
            $imageId = $this->db->insert(
                "INSERT INTO court_images (court_id, image_path, image_type, caption, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())",
                [$courtId, $uploadResult['path'], $imageType, $caption]
            );

            $this->log('info', 'Court image uploaded successfully', ['image_id' => $imageId]);

            $this->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image' => [
                    'id' => $imageId,
                    'path' => $uploadResult['path'],
                    'url' => $uploadResult['url'],
                    'type' => $imageType,
                    'caption' => $caption
                ]
            ]);

        } catch (\Exception $e) {
            $this->log('error', 'Court image upload exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete court image
     */
    public function deleteCourtImage(Request $request): void
    {
        try {
            $imageId = $request->param('id');

            $image = $this->db->fetch(
                "SELECT ci.*, v.owner_id FROM court_images ci 
                 JOIN courts c ON ci.court_id = c.id
                 JOIN venues v ON c.venue_id = v.id 
                 WHERE ci.id = ?",
                [$imageId]
            );

            if (!$image) {
                $this->json(['success' => false, 'message' => 'Image not found'], 404);
                return;
            }

            // Check ownership
            if ($this->user()['role'] === 'venue_owner' && $image['owner_id'] != $this->user()['id']) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
                return;
            }

            // Delete file
            $filePath = $this->uploadsPath . '/' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete record
            $this->db->execute("DELETE FROM court_images WHERE id = ?", [$imageId]);

            $this->json(['success' => true, 'message' => 'Image deleted successfully']);

        } catch (\Exception $e) {
            $this->log('error', 'Court image deletion failed', ['error' => $e->getMessage()]);
            $this->json(['success' => false, 'message' => 'Deletion failed'], 500);
        }
    }

    /**
     * Update court image
     */
    public function updateCourtImage(Request $request): void
    {
        try {
            $imageId = $request->param('id');
            $caption = $request->input('caption', '');
            $imageType = $request->input('image_type', 'gallery');
            $sortOrder = $request->input('sort_order', 0);

            $image = $this->db->fetch(
                "SELECT ci.*, v.owner_id FROM court_images ci 
                 JOIN courts c ON ci.court_id = c.id
                 JOIN venues v ON c.venue_id = v.id 
                 WHERE ci.id = ?",
                [$imageId]
            );

            if (!$image) {
                $this->json(['success' => false, 'message' => 'Image not found'], 404);
                return;
            }

            // Check ownership
            if ($this->user()['role'] === 'venue_owner' && $image['owner_id'] != $this->user()['id']) {
                $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
                return;
            }

            $this->db->execute(
                "UPDATE court_images SET caption = ?, image_type = ?, sort_order = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$caption, $imageType, $sortOrder, $imageId]
            );

            $this->json(['success' => true, 'message' => 'Image updated successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Update failed'], 500);
        }
    }

    // â”€â”€ Helper Methods â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Upload and validate image file
     */
    private function uploadImage(array $file, string $type = 'venues'): array
    {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
        }

        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File too large (max 5MB)'];
        }

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type (only JPG, PNG, WEBP allowed)'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $relativePath = $type . '/' . $filename;
        $fullPath = $this->uploadsPath . '/' . $relativePath;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['success' => false, 'message' => 'Failed to save file'];
        }

        // Optional: Resize/optimize image
        $this->optimizeImage($fullPath, $mimeType);

        return [
            'success' => true,
            'path' => $relativePath,
            'url' => uploads_url($relativePath)
        ];
    }

    /**
     * Optimize uploaded image (resize if too large, compress)
     */
    private function optimizeImage(string $path, string $mimeType): void
    {
        if (!function_exists('getimagesize') || !function_exists('imagecreatetruecolor')) {
            return;
        }

        try {
            $sizes = @\getimagesize($path);
            if (!$sizes)
                return;
            list($width, $height) = $sizes;

            // Skip if already small enough
            if ($width <= 1920 && $height <= 1920) {
                return;
            }

            // Calculate new dimensions
            $maxDimension = 1920;
            if ($width > $height) {
                $newWidth = (int) $maxDimension;
                $newHeight = (int) (($height / $width) * $maxDimension);
            } else {
                $newHeight = (int) $maxDimension;
                $newWidth = (int) (($width / $height) * $maxDimension);
            }

            $source = null;
            // Create image resource
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    if (function_exists('imagecreatefromjpeg')) {
                        $source = @\imagecreatefromjpeg($path);
                    }
                    break;
                case 'image/png':
                    if (function_exists('imagecreatefrompng')) {
                        $source = @\imagecreatefrompng($path);
                    }
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $source = @\imagecreatefromwebp($path);
                    }
                    break;
                default:
                    return;
            }

            if (!$source)
                return;

            // Create resized image
            $thumb = \imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG
            if ($mimeType === 'image/png') {
                \imagealphablending($thumb, false);
                \imagesavealpha($thumb, true);
            }

            \imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save optimized image
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    if (function_exists('imagejpeg'))
                        \imagejpeg($thumb, $path, 85);
                    break;
                case 'image/png':
                    if (function_exists('imagepng'))
                        \imagepng($thumb, $path, 8);
                    break;
                case 'image/webp':
                    if (function_exists('imagewebp'))
                        \imagewebp($thumb, $path, 85);
                    break;
            }

            @\imagedestroy($source);
            @\imagedestroy($thumb);

        } catch (\Throwable $e) {
            // Silently fail - optimization is non-critical
            $this->log('warning', 'Image optimization skipped', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Helper to log events
     */
    private function log(string $level, string $message, array $context = []): void
    {
        // Simple logging implementation
        $logFile = __DIR__ . '/../../storage/logs/' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logMessage = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
