<?php
/**
 * COMPLETE DATABASE SETUP FOR RAILWAY
 * ===================================
 * Run this ONCE after deployment
 * Then DELETE this file!
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Setup</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;}</style></head><body>";
echo "<h1>🗄️ Setting Up Complete Database...</h1>";

$db = getDB();

// Check if already setup
$check = $db->query("SHOW TABLES LIKE 'users'");
if ($check && $check->num_rows > 0) {
    die("<h2 class='error'>⚠️ Database Already Setup!</h2><p>Tables exist. Delete this file for security!</p></body></html>");
}

$queries = [
    // Users table
    "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50) UNIQUE NOT NULL,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        status ENUM('active', 'banned') DEFAULT 'active',
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        expire_date DATE NULL,
        permissions VARCHAR(255) DEFAULT 'basic',
        hwid VARCHAR(255) DEFAULT NULL,
        hardware_id VARCHAR(255) DEFAULT NULL,
        device_id VARCHAR(255) DEFAULT NULL,
        last_login DATETIME DEFAULT NULL,
        login_attempts INT DEFAULT 0,
        last_attempt DATETIME DEFAULT NULL,
        temp_banned TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // Activity logs table
    "CREATE TABLE activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // Login logs table
    "CREATE TABLE login_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        device_id VARCHAR(255),
        ip_address VARCHAR(45),
        status ENUM('success', 'failed', 'invalid', 'banned') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // Hardware bindings table
    "CREATE TABLE hardware_bindings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        hardware_id VARCHAR(255) NOT NULL,
        device_id VARCHAR(255),
        ip_address VARCHAR(45),
        last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_binding (username, hardware_id),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // Bot versions table
    "CREATE TABLE bot_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        version VARCHAR(20) NOT NULL,
        required TINYINT DEFAULT 1,
        force_update TINYINT DEFAULT 0,
        download_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

echo "<h2>📝 Creating Tables...</h2>";

foreach ($queries as $i => $query) {
    echo "<p>Creating table " . ($i + 1) . "...</p>";
    if ($db->query($query)) {
        echo "<p class='success'>✓ Success</p>";
    } else {
        echo "<p class='error'>✗ Error: " . $db->error . "</p>";
        die("</body></html>");
    }
}

// Insert default admin
echo "<h2>👤 Creating Admin User...</h2>";
$hashedPassword = password_hash('AliHamza@2025', PASSWORD_DEFAULT);
$userId = 'ADMIN001';
$username = 'admin';
$password = 'AliHamza@2025'; // Plain password for bot login
$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime('+365 days'));
$status = 'active';

$stmt = $db->prepare("INSERT INTO users (user_id, username, password, start_date, end_date, status, permissions) VALUES (?, ?, ?, ?, ?, ?, 'admin')");
$stmt->bind_param("ssssss", $userId, $username, $password, $startDate, $endDate, $status);

if ($stmt->execute()) {
    echo "<p class='success'>✓ Admin user created</p>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> AliHamza@2025</p>";
} else {
    echo "<p class='error'>✗ Failed to create admin</p>";
}

// Insert default bot version
$stmt = $db->prepare("INSERT INTO bot_versions (version, required, force_update) VALUES ('5.0.0', 1, 0)");
$stmt->execute();
echo "<p class='success'>✓ Default bot version set</p>";

echo "<hr>";
echo "<h1 class='success'>🎉 DATABASE SETUP COMPLETE!</h1>";
echo "<h2>🔑 Login Credentials:</h2>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> AliHamza@2025</p>";
echo "<hr>";
echo "<p style='color:#ff4444;font-weight:bold;font-size:1.2em'>⚠️ CRITICAL: DELETE THIS FILE (setup_complete.php) NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold'>Go to Panel →</a></p>";
echo "</body></html>";
?>
