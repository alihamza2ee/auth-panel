<?php
/**
 * FIX MISSING DATABASE COLUMNS
 * ============================
 * Run this to add missing columns that InfinityFree files need
 * DELETE after running!
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Database</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;}</style></head><body>";
echo "<h1>🔧 Fixing Database Schema...</h1>";

$db = getDB();

// Add missing columns to users table
$fixes = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS permissions VARCHAR(255) DEFAULT 'basic' AFTER end_date",
    "ALTER TABLE users MODIFY COLUMN hwid VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users MODIFY COLUMN hardware_id VARCHAR(255) DEFAULT NULL"
];

echo "<h2>📝 Adding Missing Columns...</h2>";

foreach ($fixes as $i => $query) {
    echo "<p>Running fix " . ($i + 1) . "...</p>";
    
    // For MySQL compatibility - use different approach
    if (strpos($query, 'IF NOT EXISTS') !== false) {
        // Check if column exists first
        $column = '';
        if (strpos($query, 'permissions') !== false) $column = 'permissions';
        
        if ($column) {
            $check = $db->query("SHOW COLUMNS FROM users LIKE '$column'");
            if ($check && $check->num_rows > 0) {
                echo "<p class='success'>✓ Column '$column' already exists</p>";
                continue;
            }
            
            // Remove IF NOT EXISTS for MySQL
            $query = str_replace(' IF NOT EXISTS', '', $query);
        }
    }
    
    if ($db->query($query)) {
        echo "<p class='success'>✓ Success</p>";
    } else {
        // Not critical if MODIFY fails
        if (strpos($query, 'MODIFY') !== false) {
            echo "<p style='color:#ffaa00'>⚠ Warning: " . $db->error . " (not critical)</p>";
        } else {
            echo "<p class='error'>✗ Error: " . $db->error . "</p>";
        }
    }
}

echo "<hr>";
echo "<h1 class='success'>🎉 DATABASE FIXED!</h1>";
echo "<p style='color:#ff4444;font-weight:bold;font-size:1.2em'>⚠️ DELETE THIS FILE (fix_database.php) NOW!</p>";
echo "<p><a href='advanced_index.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold'>Go to Panel →</a></p>";
echo "</body></html>";
?>
