<?php
/**
 * DATABASE DIAGNOSTIC
 * ==================
 * Check what's in the database
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Check</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} pre{background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;overflow-x:auto;}</style></head><body>";
echo "<h1>🔍 Database Diagnostic</h1>";

$db = getDB();

// List all tables
echo "<h2>📋 Tables in Database:</h2>";
$result = $db->query("SHOW TABLES");
$tables = [];

if ($result) {
    echo "<pre>";
    while ($row = $result->fetch_array()) {
        $table = $row[0];
        $tables[] = $table;
        echo "✓ " . $table . "\n";
    }
    echo "</pre>";
} else {
    echo "<p style='color:#ff4444'>Error: " . $db->error . "</p>";
}

// If users table exists, show structure
if (in_array('users', $tables)) {
    echo "<h2>📊 Users Table Structure:</h2>";
    $result = $db->query("SHOW COLUMNS FROM users");
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-25s %-20s %s\n", $row['Field'], $row['Type'], $row['Null']);
    }
    echo "</pre>";
    
    // Count records
    $count = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
    echo "<p><strong>Total Records:</strong> $count</p>";
}

echo "<hr>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:bold'>Go to Panel →</a></p>";
echo "</body></html>";
?>
