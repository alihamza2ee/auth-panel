<?php
/**
 * COMPLETE DATABASE FIX - ALL MISSING COLUMNS
 * ===========================================
 * Adds ALL missing columns needed by API
 * Run ONCE then DELETE!
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Complete Database Fix</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;} .warning{color:#ffaa00;}</style></head><body>";
echo "<h1>🔧 Complete Database Fix - Adding ALL Missing Columns</h1>";

$db = getDB();

// Function to check if column exists
function columnExists($db, $table, $column) {
    $result = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Function to add column safely
function addColumn($db, $table, $column, $definition) {
    echo "<p>Checking: <strong>$table.$column</strong>...</p>";
    
    if (columnExists($db, $table, $column)) {
        echo "<p class='warning'>⚠ Already exists, skipping</p>";
        return true;
    }
    
    $query = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
    
    if ($db->query($query)) {
        echo "<p class='success'>✓ Added successfully!</p>";
        return true;
    } else {
        echo "<p class='error'>✗ Error: " . $db->error . "</p>";
        return false;
    }
}

echo "<h2>📝 Adding Missing Columns to 'users' Table...</h2>";

// Users table missing columns
$userColumns = [
    ['column' => 'expire_date', 'definition' => 'DATE NULL'],
    ['column' => 'permissions', 'definition' => 'VARCHAR(255) DEFAULT "basic"'],
    ['column' => 'login_attempts', 'definition' => 'INT DEFAULT 0'],
    ['column' => 'last_attempt', 'definition' => 'DATETIME NULL'],
    ['column' => 'temp_banned', 'definition' => 'TINYINT DEFAULT 0'],
    ['column' => 'hardware_id', 'definition' => 'VARCHAR(255) NULL'],
    ['column' => 'device_id', 'definition' => 'VARCHAR(255) NULL']
];

$success = true;
foreach ($userColumns as $col) {
    if (!addColumn($db, 'users', $col['column'], $col['definition'])) {
        $success = false;
    }
}

// Show current users table structure
echo "<h2>📊 Current 'users' Table Structure:</h2>";
$result = $db->query("SHOW COLUMNS FROM users");
echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;overflow-x:auto;'>";
echo sprintf("%-25s %-30s %-10s\n", "Column", "Type", "Null");
echo str_repeat("-", 70) . "\n";
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-25s %-30s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
}
echo "</pre>";

// Update ali user to set default values for new columns
echo "<h2>🔄 Updating 'ali' user with default values...</h2>";
$updateQuery = "UPDATE users SET 
    temp_banned = 0, 
    login_attempts = 0 
    WHERE username = 'ali'";

if ($db->query($updateQuery)) {
    echo "<p class='success'>✓ User 'ali' updated successfully!</p>";
} else {
    echo "<p class='error'>✗ Error updating user: " . $db->error . "</p>";
}

echo "<hr>";

if ($success) {
    echo "<h1 class='success'>🎉 ALL COLUMNS ADDED SUCCESSFULLY!</h1>";
    echo "<h2>✅ Database is now fully compatible with API!</h2>";
} else {
    echo "<h1 class='error'>⚠️ SOME ERRORS OCCURRED</h1>";
    echo "<p>Check errors above. Some columns might already exist.</p>";
}

echo "<p style='color:#ff4444;font-weight:bold;font-size:1.2em;margin-top:30px;'>⚠️ CRITICAL: DELETE THIS FILE NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold;'>Go to Panel →</a></p>";
echo "</body></html>";
?>
