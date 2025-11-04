<?php
/**
 * FIX login_logs.status COLUMN
 * ============================
 * Add missing ENUM values
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Status Column</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;}</style></head><body>";
echo "<h1>🔧 Fixing status Column...</h1>";

$db = getDB();

// Show current ENUM values
echo "<h2>📋 Current Structure:</h2>";
$result = $db->query("SHOW COLUMNS FROM login_logs WHERE Field = 'status'");
if ($row = $result->fetch_assoc()) {
    echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
    echo "Current Type: " . $row['Type'] . "\n";
    echo "</pre>";
}

// Update ENUM to include all needed values
$query = "ALTER TABLE login_logs MODIFY COLUMN status ENUM('success', 'failed', 'invalid', 'banned') NOT NULL";

echo "<p>Updating status column to include all values...</p>";

if ($db->query($query)) {
    echo "<p class='success'>✓ Status column updated successfully!</p>";
    
    // Show new structure
    echo "<h2>✅ New Structure:</h2>";
    $result = $db->query("SHOW COLUMNS FROM login_logs WHERE Field = 'status'");
    if ($row = $result->fetch_assoc()) {
        echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
        echo "New Type: " . $row['Type'] . "\n";
        echo "</pre>";
    }
} else {
    echo "<p class='error'>✗ Error: " . $db->error . "</p>";
}

echo "<hr>";
echo "<h1 class='success'>🎉 FIXED!</h1>";
echo "<p style='color:#ff4444;font-weight:bold;'>⚠️ DELETE THIS FILE NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold'>Go to Panel →</a></p>";
echo "</body></html>";
?>
