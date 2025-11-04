<?php
/**
 * SET DEFAULT VERSION - Run this once
 * ====================================
 */

require_once 'advanced_config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Set Version</title>";
echo "<style>body{font-family:system-ui;background:#0a0e1a;color:#fff;padding:40px;max-width:800px;margin:0 auto;} .success{color:#00ff88;padding:20px;background:rgba(0,255,136,0.1);border:2px solid #00ff88;border-radius:10px;margin:20px 0;} .info{color:#0088ff;background:rgba(0,136,255,0.1);padding:15px;border-radius:8px;margin:15px 0;} code{background:rgba(0,0,0,0.5);padding:3px 8px;border-radius:4px;}</style></head><body>";

echo "<h1>🔄 Version Control Setup</h1>";

$db = getDB();

// Check if version already exists
$existing = $db->query("SELECT * FROM bot_versions ORDER BY id DESC LIMIT 1")->fetch_assoc();

if ($existing) {
    echo "<div class='info'>";
    echo "<strong>⚠️ Version Already Set:</strong><br>";
    echo "Version: <code>{$existing['version']}</code><br>";
    echo "Force Update: " . ($existing['force_update'] ? '✅ Enabled' : '❌ Disabled') . "<br>";
    echo "Download URL: " . ($existing['download_url'] ?: 'Not set') . "<br><br>";
    echo "Go to Settings page to update.";
    echo "</div>";
} else {
    // Insert default version
    $default_version = "5.0.0";
    $force_update = 0; // Disabled by default
    $download_url = "";
    
    $stmt = $db->prepare("INSERT INTO bot_versions (version, required, force_update, download_url) VALUES (?, 1, ?, ?)");
    $stmt->bind_param("sis", $default_version, $force_update, $download_url);
    
    if ($stmt->execute()) {
        echo "<div class='success'>";
        echo "<h2>✅ Version Set Successfully!</h2>";
        echo "Default Version: <code>$default_version</code><br>";
        echo "Force Update: ❌ Disabled<br>";
        echo "Status: Bots with v5.0.0 can run";
        echo "</div>";
        
        echo "<div class='info'>";
        echo "<strong>📝 Next Steps:</strong><br>";
        echo "1. Go to Settings page to configure<br>";
        echo "2. Enable 'Force Update' when ready<br>";
        echo "3. Update version number for new releases";
        echo "</div>";
    } else {
        echo "<p style='color:#ff4444;'>❌ Error: " . $db->error . "</p>";
    }
}

echo "<hr style='margin:30px 0;border-color:rgba(255,255,255,0.2);'>";

echo "<h2>🎯 Test Scenarios:</h2>";
echo "<div class='info'>";
echo "<strong>SCENARIO 1: Allow All Bots</strong><br>";
echo "Version: 5.0.0 | Force: ❌ Disabled<br>";
echo "Result: All bots can run<br><br>";

echo "<strong>SCENARIO 2: Force Update</strong><br>";
echo "Version: 6.0.0 | Force: ✅ Enabled<br>";
echo "Result: Only v6.0.0 bots can run<br><br>";

echo "<strong>SCENARIO 3: Optional Update</strong><br>";
echo "Version: 5.5.0 | Force: ❌ Disabled<br>";
echo "Result: All bots run, v5.0.0 sees update notice";
echo "</div>";

echo "<p style='margin-top:30px;'><a href='settings.php' style='background:#00ff88;color:#000;padding:15px 30px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:bold;'>⚙️ Go to Settings →</a></p>";

echo "<p style='color:#ff4444;margin-top:30px;font-weight:bold;'>⚠️ DELETE THIS FILE AFTER RUNNING!</p>";

echo "</body></html>";
?>
