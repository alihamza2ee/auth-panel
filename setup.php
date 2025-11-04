<?php
/**
 * Database Setup Script
 * Visit: your-url.railway.app/setup.php to run
 * DELETE THIS FILE after setup!
 */

// Include config
require_once 'advanced_config.php';

// Prevent running twice
$checkTable = getDB()->query("SHOW TABLES LIKE 'users'");
if ($checkTable && $checkTable->num_rows > 0) {
    die("<h1>✅ Database Already Setup!</h1><p>Tables already exist. Delete this file for security!</p>");
}

echo "<h1>🗄️ Setting Up Database...</h1>";

// SQL queries
$queries = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50) UNIQUE NOT NULL,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        hwid VARCHAR(255) DEFAULT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('active', 'expired', 'disabled') DEFAULT 'active',
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // Activity logs table
    "CREATE TABLE IF NOT EXISTS activity_logs (
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
    
    // Settings table
    "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

$db = getDB();

// Run queries
foreach ($queries as $i => $query) {
    echo "<p>Running query " . ($i + 1) . "...</p>";
    if ($db->query($query)) {
        echo "<p style='color:green'>✓ Success</p>";
    } else {
        echo "<p style='color:red'>✗ Error: " . $db->error . "</p>";
        die();
    }
}

// Insert default data
echo "<h2>📝 Inserting Default Data...</h2>";

// Default admin (password: AliHamza@2025)
$hashedPassword = password_hash('AliHamza@2025', PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT INTO users (user_id, username, password, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
$userId = 'ADMIN001';
$username = 'admin';
$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime('+365 days'));
$status = 'active';
$stmt->bind_param("ssssss", $userId, $username, $hashedPassword, $startDate, $endDate, $status);

if ($stmt->execute()) {
    echo "<p style='color:green'>✓ Default admin user created</p>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> AliHamza@2025</p>";
} else {
    echo "<p style='color:orange'>⚠ Admin user may already exist</p>";
}

// Default settings
$settings = [
    ['bot_version', '5.0.0'],
    ['force_update', 'false'],
    ['update_url', ''],
    ['enable_hardware_lock', 'true'],
    ['enable_ip_logging', 'true'],
    ['max_login_attempts', '5']
];

$stmt = $db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($settings as $setting) {
    $stmt->bind_param("ss", $setting[0], $setting[1]);
    $stmt->execute();
}
echo "<p style='color:green'>✓ Default settings inserted</p>";

echo "<hr>";
echo "<h1 style='color:green'>🎉 DATABASE SETUP COMPLETE!</h1>";
echo "<h2>🔑 Login Credentials:</h2>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> AliHamza@2025</p>";
echo "<hr>";
echo "<p style='color:red; font-weight:bold'>⚠️ IMPORTANT: DELETE THIS FILE (setup.php) NOW FOR SECURITY!</p>";
echo "<p><a href='advanced_index.php' style='font-size:20px; background:blue; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Panel →</a></p>";
?>
