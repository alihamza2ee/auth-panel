<?php
/**
 * FIX DATABASE SCHEMA - IMPROVED VERSION
 * ======================================
 * Adds missing columns safely
 * DELETE after running!
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Database</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;} .warning{color:#ffaa00;}</style></head><body>";
echo "<h1>🔧 Fixing Database Schema...</h1>";

$db = getDB();

// Function to check if column exists
function columnExists($db, $table, $column) {
    $result = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

echo "<h2>📝 Checking and Adding Missing Columns...</h2>";

// List of columns to add
$columns = [
    ['table' => 'users', 'column' => 'permissions', 'definition' => 'VARCHAR(255) DEFAULT "basic"'],
    ['table' => 'users', 'column' => 'expire_date', 'definition' => 'DATE NULL'],
    ['table' => 'users', 'column' => 'login_attempts', 'definition' => 'INT DEFAULT 0'],
    ['table' => 'users', 'column' => 'last_attempt', 'definition' => 'DATETIME NULL'],
    ['table' => 'users', 'column' => 'temp_banned', 'definition' => 'TINYINT DEFAULT 0']
];

foreach ($columns as $col) {
    $table = $col['table'];
    $column = $col['column'];
    $definition = $col['definition'];
    
    echo "<p>Checking column: <strong>$table.$column</strong>...</p>";
    
    if (columnExists($db, $table, $column)) {
        echo "<p class='warning'>⚠ Column already exists, skipping</p>";
    } else {
        $query = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        
        if ($db->query($query)) {
            echo "<p class='success'>✓ Added column successfully!</p>";
        } else {
            echo "<p class='error'>✗ Error: " . $db->error . "</p>";
        }
    }
}

// Show current table structure
echo "<h2>📋 Current Users Table Structure:</h2>";
$result = $db->query("SHOW COLUMNS FROM users");
echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
echo "</pre>";

echo "<hr>";
echo "<h1 class='success'>🎉 DATABASE CHECK COMPLETE!</h1>";
echo "<p style='color:#ff4444;font-weight:bold;font-size:1.2em'>⚠️ DELETE THIS FILE (fix_database.php) NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold'>Go to Panel →</a></p>";
echo "</body></html>";
?>
