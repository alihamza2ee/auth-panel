<?php
require_once 'advanced_config.php';
if (!isLoggedIn()) { header('Location: advanced_index.php'); exit(); }

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_version'])) {
        $version = trim($_POST['version']);
        $force = isset($_POST['force_update']) ? 1 : 0;
        $url = trim($_POST['download_url']);
        
        $stmt = $db->prepare("INSERT INTO bot_versions (version, force_update, download_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $version, $force, $url);
        $stmt->execute();
        
        logActivity('version_updated', "Version: $version, Force: $force", ADMIN_USER);
        $success = "Version settings updated!";
    }
}

$latest_version = $db->query("SELECT * FROM bot_versions ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui;
            background: linear-gradient(135deg, #0a0e1a 0%, #1a1f2e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .panel {
            background: rgba(255,255,255,0.05);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        h1 { color: #FFD700; margin-bottom: 20px; }
        h2 { color: #00ff88; margin-bottom: 15px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #aaa; }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: #fff;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
        }
        .btn-primary { background: linear-gradient(135deg, #00ff88, #00cc66); color: #000; }
        .btn-secondary { background: #666; color: #fff; text-decoration: none; display: inline-block; }
        .info-box {
            background: rgba(0,136,255,0.1);
            border: 1px solid #0088ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success-box {
            background: rgba(0,255,136,0.1);
            border: 1px solid #00ff88;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Settings & Configuration</h1>
        
        <?php if (isset($success)): ?>
            <div class="success-box"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="panel">
            <h2>🔄 Bot Version Control</h2>
            
            <?php if ($latest_version): ?>
                <div class="info-box">
                    <strong>Current Settings:</strong><br>
                    Version: <?php echo $latest_version['version']; ?><br>
                    Force Update: <?php echo $latest_version['force_update'] ? 'Enabled ✅' : 'Disabled ❌'; ?><br>
                    Download URL: <?php echo $latest_version['download_url'] ?: 'Not set'; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Required Bot Version *</label>
                    <input type="text" name="version" value="<?php echo $latest_version['version'] ?? '5.0.0'; ?>" required>
                    <small style="color: #888;">Example: 5.0.0</small>
                </div>
                
                <div class="form-group">
                    <label>Download URL</label>
                    <input type="text" name="download_url" value="<?php echo $latest_version['download_url'] ?? ''; ?>" placeholder="https://yoursite.com/bot_update.zip">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="force_update">
                        <span>Force Update (Bot will stop if version is old)</span>
                    </label>
                </div>
                
                <button type="submit" name="update_version" class="btn btn-primary">💾 Save Settings</button>
                <a href="advanced_index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        
        <div class="panel">
            <h2>🔒 Security Settings</h2>
            <p style="color: #aaa; margin-bottom: 15px;">Current configuration from config.php:</p>
            <ul style="line-height: 2;">
                <li>Hardware Lock: <strong><?php echo ENABLE_HARDWARE_LOCK ? '✅ Enabled' : '❌ Disabled'; ?></strong></li>
                <li>IP Logging: <strong><?php echo ENABLE_IP_LOGGING ? '✅ Enabled' : '❌ Disabled'; ?></strong></li>
                <li>Max Login Attempts: <strong><?php echo MAX_LOGIN_ATTEMPTS; ?></strong></li>
            </ul>
            <p style="color: #888; margin-top: 15px; font-size: 0.9em;">
                To change these settings, edit advanced_config.php file.
            </p>
        </div>
    </div>
</body>
</html>
