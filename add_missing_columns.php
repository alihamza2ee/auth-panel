<?php
/**
 * ADD MISSING device_id COLUMN
 * ============================
 * Run once, then DELETE!
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Add Missing Column</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;}</style></head><body>";
echo "<h1>🔧 Adding Missing Columns...</h1>";

$db = getDB();

// Function to check if column exists
function columnExists($db, $table, $column) {
    $result = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Columns to add
$columns = [
    ['table' => 'login_logs', 'column' => 'device_id', 'definition' => 'VARCHAR(255) NULL AFTER username'],
    ['table' => 'login_logs', 'column' => 'user_agent', 'definition' => 'TEXT NULL AFTER device_id']
];

foreach ($columns as $col) {
    $table = $col['table'];
    $column = $col['column'];
    $definition = $col['definition'];
    
    echo "<p>Checking: <strong>$table.$column</strong>...</p>";
    
    if (columnExists($db, $table, $column)) {
        echo "<p style='color:#ffaa00'>⚠ Already exists, skipping</p>";
    } else {
        $query = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        
        if ($db->query($query)) {
            echo "<p class='success'>✓ Added successfully!</p>";
        } else {
            echo "<p class='error'>✗ Error: " . $db->error . "</p>";
        }
    }
}

// Show current structure
echo "<h2>📋 login_logs Table Structure:</h2>";
$result = $db->query("SHOW COLUMNS FROM login_logs");
echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-20s %-20s %s\n", $row['Field'], $row['Type'], $row['Null']);
}
echo "</pre>";

echo "<hr>";
echo "<h1 class='success'>🎉 COLUMNS ADDED!</h1>";
echo "<p style='color:#ff4444;font-weight:bold;'>⚠️ DELETE THIS FILE NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold'>Go to Panel →</a></p>";
echo "</body></html>";
?>
