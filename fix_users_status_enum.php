<?php
/**
 * FIX users.status ENUM
 * =====================
 * Add 'banned' to allowed values
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Status</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;}</style></head><body>";
echo "<h1>🔧 Fixing users.status Column...</h1>";

$db = getDB();

// Check current ENUM
echo "<h2>📋 Current Status ENUM:</h2>";
$result = $db->query("SHOW COLUMNS FROM users WHERE Field = 'status'");
if ($row = $result->fetch_assoc()) {
    echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
    echo "Type: " . $row['Type'] . "\n";
    echo "</pre>";
}

// Update ENUM to include 'banned'
$query = "ALTER TABLE users MODIFY COLUMN status ENUM('active', 'banned', 'expired', 'disabled') DEFAULT 'active'";

echo "<p>Updating status column...</p>";

if ($db->query($query)) {
    echo "<p class='success'>✅ Status column updated!</p>";
    
    // Show new structure
    echo "<h2>✅ New Status ENUM:</h2>";
    $result = $db->query("SHOW COLUMNS FROM users WHERE Field = 'status'");
    if ($row = $result->fetch_assoc()) {
        echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
        echo "Type: " . $row['Type'] . "\n";
        echo "</pre>";
    }
} else {
    echo "<p class='error'>✗ Error: " . $db->error . "</p>";
}

echo "<hr>";
echo "<h1 class='success'>🎉 FIXED!</h1>";
echo "<p style='color:#ff4444;font-weight:bold;'>⚠️ DELETE THIS FILE NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold;'>Go to Panel →</a></p>";
echo "</body></html>";
?>
