<?php
/**
 * ADD MISSING bot_versions TABLE
 * ==============================
 * Run once, then DELETE!
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Add Bot Versions Table</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;} .success{color:#00ff88;} .error{color:#ff4444;}</style></head><body>";
echo "<h1>🔧 Adding bot_versions Table...</h1>";

$db = getDB();

// Create bot_versions table
$query = "CREATE TABLE IF NOT EXISTS bot_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(20) NOT NULL,
    required TINYINT DEFAULT 1,
    force_update TINYINT DEFAULT 0,
    download_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

echo "<p>Creating bot_versions table...</p>";

if ($db->query($query)) {
    echo "<p class='success'>✓ Table created successfully!</p>";
    
    // Insert default version
    $stmt = $db->prepare("INSERT INTO bot_versions (version, required, force_update) VALUES ('5.0.0', 1, 0)");
    if ($stmt->execute()) {
        echo "<p class='success'>✓ Default version (5.0.0) added!</p>";
    }
    
} else {
    echo "<p class='error'>✗ Error: " . $db->error . "</p>";
}

echo "<hr>";
echo "<h1 class='success'>🎉 TABLE ADDED!</h1>";
echo "<p style='color:#ff4444;font-weight:bold;'>⚠️ DELETE THIS FILE NOW!</p>";
echo "<p><a href='settings.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;margin-top:20px;font-weight:bold'>Go to Settings →</a></p>";
echo "</body></html>";
?>
