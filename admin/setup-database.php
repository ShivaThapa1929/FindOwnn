<?php
/**
 * Database Setup Script
 * Creates database and runs all migrations
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🚀 Findownn Database Setup\n";
echo "====================================\n\n";

// Load .env file
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("❌ Error: .env file not found!\n");
}

$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line && $line[0] !== '#' && strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$dbName = $env['DB_DATABASE'] ?? 'findownn_admin';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

echo "📋 Configuration:\n";
echo "   Host: $host\n";
echo "   Database: $dbName\n";
echo "   User: $user\n\n";

try {
    // Step 1: Connect without database to create it
    echo "1️⃣ Connecting to MySQL...\n";
    $pdo = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "   ✅ Connected successfully!\n\n";

    // Step 2: Create database if not exists
    echo "2️⃣ Creating database '$dbName'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✅ Database created/verified!\n\n";

    // Step 3: Select the database
    $pdo->exec("USE `$dbName`");

    // Step 4: Run migrations
    $migrationsDir = __DIR__ . '/database/migrations';
    $migrations = glob($migrationsDir . '/*.sql');
    sort($migrations);

    echo "3️⃣ Running migrations...\n";
    foreach ($migrations as $file) {
        $filename = basename($file);
        echo "   📄 Running: $filename\n";

        $sql = file_get_contents($file);
        
        // Split by semicolons but be careful with delimiters
        $statements = [];
        $current = '';
        $inDelimiter = false;
        
        foreach (explode("\n", $sql) as $line) {
            $trimmed = trim($line);
            
            // Skip comments
            if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
                continue;
            }
            
            // Check for DELIMITER commands
            if (stripos($trimmed, 'DELIMITER') === 0) {
                $inDelimiter = !$inDelimiter;
                continue;
            }
            
            $current .= $line . "\n";
            
            // If not in delimiter block and line ends with semicolon
            if (!$inDelimiter && substr($trimmed, -1) === ';') {
                $statements[] = trim($current);
                $current = '';
            }
        }
        
        // Add last statement if any
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }

        // Execute each statement
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "      ⚠️  Warning: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "      ✅ Completed\n";
    }
    
    echo "\n4️⃣ Verifying tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✅ Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "      - $table\n";
    }

    // Step 5: Create default admin user if not exists
    echo "\n5️⃣ Creating default admin user...\n";
    
    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@findownn.com'")->fetchColumn();
    
    if ($checkAdmin == 0) {
        $password = password_hash('password', PASSWORD_BCRYPT);
        $apiToken = md5('admin@findownn.com' . time());
        
        $pdo->exec("
            INSERT INTO users (name, email, password, role, status, api_token, created_at) 
            VALUES (
                'Super Admin',
                'admin@findownn.com',
                '$password',
                'super_admin',
                'active',
                '$apiToken',
                NOW()
            )
        ");
        
        echo "   ✅ Admin user created!\n";
        echo "      Email: admin@findownn.com\n";
        echo "      Password: password\n";
        echo "      ⚠️  CHANGE THIS PASSWORD AFTER FIRST LOGIN!\n";
    } else {
        echo "   ℹ️  Admin user already exists\n";
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 DATABASE SETUP COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "📊 Summary:\n";
    echo "   ✅ Database created: $dbName\n";
    echo "   ✅ Migrations run: " . count($migrations) . "\n";
    echo "   ✅ Tables created: " . count($tables) . "\n";
    echo "   ✅ Admin user ready\n\n";
    
    echo "🔗 Next Steps:\n";
    echo "   1. Access admin: http://localhost:8000/admin/\n";
    echo "   2. Login: admin@findownn.com / password\n";
    echo "   3. Change password immediately!\n";
    echo "   4. Test API: http://localhost:8000/api/v1/venues\n\n";
    
    echo "✅ Ready to use!\n\n";

} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "💡 Solution:\n";
        echo "   - Check your .env file\n";
        echo "   - Verify DB_USERNAME and DB_PASSWORD\n";
        echo "   - Make sure MySQL is running\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "💡 This script will create the database automatically\n";
        echo "   - Make sure MySQL user has CREATE DATABASE permission\n";
    }
    
    exit(1);
}
