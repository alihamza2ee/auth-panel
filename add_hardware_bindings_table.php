<?php
/**
 * ADD MISSING hardware_bindings TABLE
 * ===================================
 * Run ONCE then DELETE!
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Add Hardware Bindings Table</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;}</style></head><body>";
echo "<h1>🔧 Adding hardware_bindings Table...</h1>";

$db = getDB();

// Check if table exists
$check = $db->query("SHOW TABLES LIKE 'hardware_bindings'");
if ($check && $check->num_rows > 0) {
    echo "<p class='error'>⚠️ Table already exists!</p>";
} else {
    // Create hardware_bindings table
    $query = "CREATE TABLE hardware_bindings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        hardware_id VARCHAR(255) NOT NULL,
        device_id VARCHAR(255),
        ip_address VARCHAR(45),
        last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_binding (username, hardware_id),
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    echo "<p>Creating hardware_bindings table...</p>";
    
    if ($db->query($query)) {
        echo "<p class='success'>✅ Table created successfully!</p>";
        
        // Show table structure
        echo "<h2>📊 Table Structure:</h2>";
        $result = $db->query("SHOW COLUMNS FROM hardware_bindings");
        echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
        echo sprintf("%-20s %-25s %-10s\n", "Column", "Type", "Null");
        echo str_repeat("-", 60) . "\n";
        while ($row = $result->fetch_assoc()) {
            echo sprintf("%-20s %-25s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Error: " . $db->error . "</p>";
    }
}

// Show all tables
echo "<h2>📋 All Database Tables:</h2>";
$result = $db->query("SHOW TABLES");
echo "<pre style='background:rgba(0,0,0,0.3);padding:15px;border-radius:8px;'>";
while ($row = $result->fetch_array()) {
    echo "✓ " . $row[0] . "\n";
}
echo "</pre>";

echo "<hr>";
echo "<h1 class='success'>🎉 HARDWARE BINDINGS TABLE ADDED!</h1>";
echo "<h2>✅ Database is NOW 100% COMPLETE!</h2>";
echo "<p style='color:#ff4444;font-weight:bold;font-size:1.2em;'>⚠️ DELETE THIS FILE NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold;'>Go to Panel →</a></p>";
echo "</body></html>";
?>
