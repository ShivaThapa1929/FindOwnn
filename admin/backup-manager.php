<?php
/**
 * Database Backup Manager
 * Lists all backups and provides backup/restore options
 */

// Load environment
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$dbName = getenv('DB_DATABASE') ?: 'findownn_admin';
$backupDir = __DIR__ . '/storage/backups';

// Ensure backup directory exists
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Get all backup files
$backups = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filepath = $backupDir . '/' . $file;
            $backups[] = [
                'filename' => $file,
                'filepath' => $filepath,
                'size' => filesize($filepath),
                'date' => filemtime($filepath)
            ];
        }
    }
}

// Format file size
function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Backup Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #0a0f0b;
            color: #f0fdf4;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: rgba(15,25,18,0.95);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #f0fdf4; 
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #a3c4af;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary { background: #10b981; color: white; }
        .btn-primary:hover { background: #059669; }
        .btn-secondary { background: #3b82f6; color: white; }
        .btn-secondary:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-outline { background: transparent; color: #a3c4af; border: 1px solid #a3c4af; }
        .btn-outline:hover { background: rgba(163,196,175,0.1); }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #0d1510;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #1a2e20;
        }
        .stat-label {
            color: #a3c4af;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .stat-value {
            color: #f0fdf4;
            font-size: 24px;
            font-weight: 700;
        }
        
        .backup-list {
            background: #0d1510;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #1a2e20;
        }
        .backup-item {
            padding: 20px;
            border-bottom: 1px solid #1a2e20;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }
        .backup-item:last-child { border-bottom: none; }
        .backup-item:hover { background: rgba(16, 185, 129, 0.05); }
        
        .backup-info {
            flex: 1;
        }
        .backup-name {
            color: #f0fdf4;
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 5px;
        }
        .backup-meta {
            color: #a3c4af;
            font-size: 13px;
            display: flex;
            gap: 20px;
        }
        .backup-actions {
            display: flex;
            gap: 10px;
        }
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a3c4af;
        }
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🗄️ Database Backup Manager</h1>
    <p class="subtitle">Manage your database backups for <?php echo htmlspecialchars($dbName); ?></p>
    
    <div class="actions">
        <a href="backup-php.php" class="btn btn-primary">
            ⚡ Create Backup (PHP)
        </a>
        <a href="backup-db-simple.php" class="btn btn-secondary">
            🔧 Create Backup (mysqldump)
        </a>
        <a href="/findownn_website/admin/dashboard" class="btn btn-outline">
            ← Back to Dashboard
        </a>
    </div>
    
    <div class="alert alert-info">
        <strong>💡 Tip:</strong> Use "PHP" method if you're having issues with mysqldump. It's slower but more reliable.
    </div>
    
    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Total Backups</div>
            <div class="stat-value"><?php echo count($backups); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Size</div>
            <div class="stat-value">
                <?php 
                $totalSize = array_sum(array_column($backups, 'size'));
                echo formatSize($totalSize);
                ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Latest Backup</div>
            <div class="stat-value">
                <?php 
                if (!empty($backups)) {
                    $latest = max(array_column($backups, 'date'));
                    $diff = time() - $latest;
                    if ($diff < 3600) {
                        echo floor($diff / 60) . 'm ago';
                    } elseif ($diff < 86400) {
                        echo floor($diff / 3600) . 'h ago';
                    } else {
                        echo floor($diff / 86400) . 'd ago';
                    }
                } else {
                    echo 'None';
                }
                ?>
            </div>
        </div>
    </div>
    
    <h2 style="margin-bottom: 15px; font-size: 20px;">Backup Files</h2>
    
    <div class="backup-list">
        <?php if (empty($backups)): ?>
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3>No Backups Found</h3>
                <p>Create your first backup using the buttons above</p>
            </div>
        <?php else: ?>
            <?php foreach ($backups as $backup): ?>
                <div class="backup-item">
                    <div class="backup-info">
                        <div class="backup-name">📄 <?php echo htmlspecialchars($backup['filename']); ?></div>
                        <div class="backup-meta">
                            <span>📦 <?php echo formatSize($backup['size']); ?></span>
                            <span>🕐 <?php echo date('M d, Y - H:i:s', $backup['date']); ?></span>
                        </div>
                    </div>
                    <div class="backup-actions">
                        <a href="/findownn_website/admin/storage/backups/<?php echo urlencode($backup['filename']); ?>" 
                           download 
                           class="btn btn-primary btn-sm">
                            📥 Download
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
