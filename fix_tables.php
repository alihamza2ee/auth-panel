<?php
/**
 * Fix Missing Tables
 * Visit: your-url.railway.app/fix_tables.php
 * DELETE THIS FILE after running!
 */

require_once 'advanced_config.php';

echo "<h1>🔧 Fixing Missing Tables...</h1>";

$db = getDB();

// Create login_logs table
$query = "CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($db->query($query)) {
    echo "<p style='color:green'>✓ login_logs table created successfully!</p>";
} else {
    echo "<p style='color:red'>✗ Error: " . $db->error . "</p>";
}

echo "<hr>";
echo "<h1 style='color:green'>🎉 FIX COMPLETE!</h1>";
echo "<p style='color:red; font-weight:bold'>⚠️ DELETE THIS FILE (fix_tables.php) NOW!</p>";
echo "<p><a href='advanced_index.php' style='font-size:20px; background:blue; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Panel →</a></p>";
?>
