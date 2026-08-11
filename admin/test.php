<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Diagnostics</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #e2e8f0;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            color: #22c55e;
            border-bottom: 2px solid #22c55e;
            padding-bottom: 10px;
        }
        .test-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #22c55e;
        }
        .success {
            color: #22c55e;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #fbbf24;
            font-weight: bold;
        }
        pre {
            background: #0a0e27;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Findownn Admin Panel - Diagnostics</h1>
        
        <?php
        // Test 1: PHP Version
        echo '<div class="test-section">';
        echo '<h2>1. PHP Version</h2>';
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.0.0', '>=')) {
            echo '<p class="success">✅ PHP ' . $phpVersion . ' (OK)</p>';
        } else {
            echo '<p class="error">❌ PHP ' . $phpVersion . ' (Needs 8.0+)</p>';
        }
        echo '</div>';
        
        // Test 2: Required Extensions
        echo '<div class="test-section">';
        echo '<h2>2. Required PHP Extensions</h2>';
        $required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
        echo '<table>';
        echo '<tr><th>Extension</th><th>Status</th></tr>';
        foreach ($required as $ext) {
            $loaded = extension_loaded($ext);
            $status = $loaded ? '<span class="success">✅ Loaded</span>' : '<span class="error">❌ Missing</span>';
            echo "<tr><td>$ext</td><td>$status</td></tr>";
        }
        echo '</table>';
        echo '</div>';
        
        // Test 3: File Paths
        echo '<div class="test-section">';
        echo '<h2>3. File Structure</h2>';
        $paths = [
            'Root' => __DIR__,
            '.env file' => __DIR__ . '/.env',
            'app/ folder' => __DIR__ . '/app',
            'public/ folder' => __DIR__ . '/public',
            'routes/ folder' => __DIR__ . '/routes',
            'storage/ folder' => __DIR__ . '/storage',
        ];
        echo '<table>';
        echo '<tr><th>Path</th><th>Status</th></tr>';
        foreach ($paths as $name => $path) {
            $exists = file_exists($path);
            $status = $exists ? '<span class="success">✅ Exists</span>' : '<span class="error">❌ Missing</span>';
            echo "<tr><td>$name</td><td>$status</td><td style='font-size:0.85em;opacity:0.7;'>$path</td></tr>";
        }
        echo '</table>';
        echo '</div>';
        
        // Test 4: .env Configuration
        echo '<div class="test-section">';
        echo '<h2>4. Environment Configuration</h2>';
        
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            echo '<p class="success">✅ .env file exists</p>';
            
            // Parse .env
            $envContent = file_get_contents($envFile);
            $envVars = [];
            foreach (explode("\n", $envContent) as $line) {
                $line = trim($line);
                if ($line && $line[0] !== '#' && strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $envVars[trim($key)] = trim($value);
                }
            }
            
            echo '<table>';
            echo '<tr><th>Variable</th><th>Value</th><th>Status</th></tr>';
            
            $important = ['APP_ENV', 'APP_DEBUG', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
            foreach ($important as $key) {
                $value = $envVars[$key] ?? '';
                $display = $key === 'DB_PASSWORD' ? '****' : $value;
                $status = !empty($value) ? '<span class="success">✅</span>' : '<span class="warning">⚠️ Empty</span>';
                echo "<tr><td>$key</td><td>$display</td><td>$status</td></tr>";
            }
            echo '</table>';
        } else {
            echo '<p class="error">❌ .env file not found</p>';
        }
        echo '</div>';
        
        // Test 5: Database Connection
        echo '<div class="test-section">';
        echo '<h2>5. Database Connection</h2>';
        
        try {
            if (file_exists($envFile)) {
                // Load env
                foreach ($envVars as $key => $value) {
                    putenv("$key=$value");
                }
                
                $host = $envVars['DB_HOST'] ?? 'localhost';
                $db = $envVars['DB_DATABASE'] ?? '';
                $user = $envVars['DB_USERNAME'] ?? 'root';
                $pass = $envVars['DB_PASSWORD'] ?? '';
                
                if (empty($db)) {
                    echo '<p class="warning">⚠️ Database name not configured</p>';
                } else {
                    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                    $pdo = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    
                    echo '<p class="success">✅ Connected to database: ' . $db . '</p>';
                    
                    // Check tables
                    $stmt = $pdo->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tables) > 0) {
                        echo '<p class="success">✅ Found ' . count($tables) . ' tables</p>';
                        echo '<details style="margin-top:10px;">';
                        echo '<summary style="cursor:pointer;color:#22c55e;">View tables</summary>';
                        echo '<pre>' . implode("\n", $tables) . '</pre>';
                        echo '</details>';
                    } else {
                        echo '<p class="warning">⚠️ Database is empty - need to run migrations</p>';
                    }
                }
            }
        } catch (PDOException $e) {
            echo '<p class="error">❌ Database connection failed</p>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                echo '<p class="warning">💡 Solution: Create the database first!</p>';
                echo '<pre>CREATE DATABASE ' . ($envVars['DB_DATABASE'] ?? 'findownn_admin') . ';</pre>';
            }
        }
        echo '</div>';
        
        // Test 6: Permissions
        echo '<div class="test-section">';
        echo '<h2>6. File Permissions</h2>';
        $writablePaths = [
            'storage/' => __DIR__ . '/storage',
            'storage/logs/' => __DIR__ . '/storage/logs',
            'storage/uploads/' => __DIR__ . '/storage/uploads',
        ];
        
        echo '<table>';
        echo '<tr><th>Path</th><th>Writable</th></tr>';
        foreach ($writablePaths as $name => $path) {
            if (file_exists($path)) {
                $writable = is_writable($path);
                $status = $writable ? '<span class="success">✅ Writable</span>' : '<span class="error">❌ Not writable</span>';
            } else {
                $status = '<span class="warning">⚠️ Not exists</span>';
            }
            echo "<tr><td>$name</td><td>$status</td></tr>";
        }
        echo '</table>';
        echo '</div>';
        
        // Test 7: URL Detection
        echo '<div class="test-section">';
        echo '<h2>7. URL Information</h2>';
        echo '<table>';
        echo '<tr><th>Variable</th><th>Value</th></tr>';
        echo '<tr><td>REQUEST_URI</td><td>' . ($_SERVER['REQUEST_URI'] ?? 'N/A') . '</td></tr>';
        echo '<tr><td>SCRIPT_NAME</td><td>' . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . '</td></tr>';
        echo '<tr><td>PHP_SELF</td><td>' . ($_SERVER['PHP_SELF'] ?? 'N/A') . '</td></tr>';
        echo '<tr><td>HTTP_HOST</td><td>' . ($_SERVER['HTTP_HOST'] ?? 'N/A') . '</td></tr>';
        echo '<tr><td>SERVER_NAME</td><td>' . ($_SERVER['SERVER_NAME'] ?? 'N/A') . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        // Summary
        echo '<div class="test-section" style="border-left-color:#22c55e;background:rgba(34,197,94,0.1);">';
        echo '<h2>✅ Next Steps</h2>';
        echo '<ol>';
        echo '<li>If database connection failed: Create database in phpMyAdmin/MySQL</li>';
        echo '<li>Run migrations: Upload SQL files from <code>database/migrations/</code></li>';
        echo '<li>Create admin user: Use SQL in documentation</li>';
        echo '<li>Access admin: <a href="/admin/" style="color:#22c55e;">/admin/</a></li>';
        echo '<li>Login with your credentials</li>';
        echo '</ol>';
        echo '</div>';
        ?>
        
        <div style="text-align:center;margin-top:40px;opacity:0.6;">
            <p>Findownn Admin Panel © 2026</p>
        </div>
    </div>
</body>
</html>
