<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * 🔥 ULTIMATE FIX SCRIPT - FINAL WORKING VERSION
 * ═══════════════════════════════════════════════════════════════
 * 
 * Upload to GitHub → Open in browser → Click ONE button → DONE!
 * 
 * NO EDITING NEEDED - Ready to use!
 * 
 * URL: https://your-app.up.railway.app/ultimate_fix_final.php
 * ⚠️ DELETE THIS FILE AFTER USE!
 * ═══════════════════════════════════════════════════════════════
 */

// ============================================
// 🔧 DATABASE CONFIG - WORKING CREDENTIALS
// ============================================
define('DB_HOST', 'mysql.railway.internal');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'iDFjnbMKzOTFBuwlZjZgzKiEBBAJDBmD');
define('DB_NAME', 'railway');

// Security password
define('FIX_PASSWORD', 'fixnow123');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 Ultimate Fix Script</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 20px;
            line-height: 1.8;
        }
        
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            text-align: center;
            padding: 40px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(255,107,107,0.3);
        }
        
        .header h1 {
            font-size: 48px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 10px;
        }
        
        .box {
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.2);
            padding: 30px;
            margin: 20px 0;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .success { color: #00ff88; font-weight: bold; }
        .error { color: #ff4444; font-weight: bold; }
        .warning { color: #ffaa00; font-weight: bold; }
        .info { color: #00aaff; }
        
        input, button {
            padding: 15px 30px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            margin: 10px 5px;
            border-radius: 8px;
            border: none;
        }
        
        input {
            background: rgba(0,0,0,0.3);
            color: #fff;
            border: 2px solid rgba(255,255,255,0.3);
            width: 400px;
        }
        
        button {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            color: #fff;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(255,107,107,0.3);
            transition: all 0.3s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(255,107,107,0.5);
        }
        
        .step {
            padding: 20px;
            margin: 15px 0;
            background: rgba(0,0,0,0.3);
            border-left: 4px solid #00ff88;
            border-radius: 5px;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .big-button {
            font-size: 24px;
            padding: 20px 50px;
            margin: 30px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        th {
            background: rgba(255,107,107,0.2);
            font-weight: bold;
        }
        
        pre {
            background: rgba(0,0,0,0.5);
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔥 ULTIMATE FIX</h1>
            <p style="font-size: 20px;">Fix Everything in 10 Seconds!</p>
        </div>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password !== FIX_PASSWORD) {
        echo '<div class="box error">❌ WRONG PASSWORD! Enter: fixnow123</div>';
        showForm();
        exit();
    }
    
    echo '<div class="box">';
    echo '<h2 class="warning">🔥 STARTING FIX...</h2>';
    echo '</div>';
    
    try {
        // STEP 1: Connect
        echo '<div class="step">';
        echo '<h3>📡 Step 1: Connecting...</h3>';
        
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($db->connect_error) {
            throw new Exception('Connection failed: ' . $db->connect_error);
        }
        
        $db->set_charset('utf8mb4');
        echo '<p class="success">✅ Connected! MySQL ' . $db->server_info . '</p>';
        echo '</div>';
        
        // STEP 2: Drop tables
        echo '<div class="step">';
        echo '<h3>🗑️ Step 2: Cleaning...</h3>';
        
        $tables = ['activity_logs', 'bot_versions', 'hardware_bindings', 'login_logs', 'settings', 'users'];
        $dropped = 0;
        
        foreach ($tables as $table) {
            if ($db->query("DROP TABLE IF EXISTS $table")) {
                echo "<p class='warning'>Removed: $table</p>";
                $dropped++;
            }
        }
        
        echo "<p class='success'>✅ Cleaned $dropped tables</p>";
        echo '</div>';
        
        // STEP 3: Create users
        echo '<div class="step">';
        echo '<h3>👥 Step 3: Users Table...</h3>';
        
        $sql = "CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) DEFAULT NULL,
            status ENUM('active','banned','expired','disabled') DEFAULT 'active',
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            hardware_id VARCHAR(255) DEFAULT NULL,
            device_id VARCHAR(255) DEFAULT NULL,
            last_login DATETIME DEFAULT NULL,
            last_ip VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($db->query($sql)) {
            echo '<p class="success">✅ Created</p>';
        } else {
            throw new Exception('Error: ' . $db->error);
        }
        echo '</div>';
        
        // STEP 4: Create versions
        echo '<div class="step">';
        echo '<h3>🔧 Step 4: Versions Table...</h3>';
        
        $sql = "CREATE TABLE bot_versions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            version VARCHAR(20) NOT NULL,
            force_update TINYINT(1) DEFAULT 0,
            download_url TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($db->query($sql)) {
            echo '<p class="success">✅ Created</p>';
        }
        echo '</div>';
        
        // STEP 5: Create logs
        echo '<div class="step">';
        echo '<h3>📝 Step 5: Logs Table...</h3>';
        
        $sql = "CREATE TABLE activity_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT DEFAULT NULL,
            ip_address VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($db->query($sql)) {
            echo '<p class="success">✅ Created</p>';
        }
        echo '</div>';
        
        // STEP 6: Add users
        echo '<div class="step">';
        echo '<h3>👤 Step 6: Adding Users...</h3>';
        
        $users = [
            ['ali', 30],
            ['test1', 60],
            ['test2', 90],
            ['hamza', 365]
        ];
        
        foreach ($users as list($username, $days)) {
            $stmt = $db->prepare("INSERT INTO users (username, status, start_date, end_date) VALUES (?, 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))");
            $stmt->bind_param("si", $username, $days);
            $stmt->execute();
            echo "<p class='success'>✅ $username ({$days} days)</p>";
        }
        echo '</div>';
        
        // STEP 7: Add version
        echo '<div class="step">';
        echo '<h3>🔢 Step 7: Bot Version...</h3>';
        
        $stmt = $db->prepare("INSERT INTO bot_versions (version, force_update) VALUES ('5.0.0', 0)");
        $stmt->execute();
        echo '<p class="success">✅ Version 5.0.0 set</p>';
        echo '</div>';
        
        // STEP 8: Verify
        echo '<div class="step">';
        echo '<h3>✅ Step 8: Verification</h3>';
        
        echo '<h4>Tables:</h4><ul>';
        $result = $db->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            echo "<li class='success'>✓ {$row[0]}</li>";
        }
        echo '</ul>';
        
        echo '<h4>Users:</h4>';
        echo '<table>';
        echo '<tr><th>Username</th><th>Status</th><th>Valid Until</th><th>Hardware</th></tr>';
        
        $result = $db->query("SELECT username, status, end_date, hardware_id FROM users");
        while ($row = $result->fetch_assoc()) {
            $hw = $row['hardware_id'] ? 'Locked' : '<span class="success">Free ✓</span>';
            echo "<tr><td><strong>{$row['username']}</strong></td><td>{$row['status']}</td><td>{$row['end_date']}</td><td>{$hw}</td></tr>";
        }
        echo '</table>';
        echo '</div>';
        
        $db->close();
        
        // SUCCESS
        echo '<div class="box" style="background: rgba(0,255,136,0.2); border: 3px solid #00ff88;">';
        echo '<h2 class="success" style="font-size: 40px; text-align: center;">🎉 DONE! 🎉</h2>';
        echo '<h3 style="text-align: center; margin: 20px;">Everything Fixed!</h3>';
        
        echo '<div style="font-size: 20px; line-height: 3; margin: 30px;">';
        echo '<p class="success">✅ Database reset</p>';
        echo '<p class="success">✅ Tables created</p>';
        echo '<p class="success">✅ Users added (all free)</p>';
        echo '<p class="success">✅ Ready to use!</p>';
        echo '</div>';
        
        echo '<hr style="margin: 30px 0; border-top: 2px solid #00ff88;">';
        
        echo '<h3 style="font-size: 28px;">🚀 NOW:</h3>';
        echo '<div style="font-size: 22px; line-height: 3;">';
        echo '<p class="error">1. DELETE this file from GitHub!</p>';
        echo '<p class="success">2. Run your bot</p>';
        echo '<p class="success">3. Login: <strong>ali</strong></p>';
        echo '<p class="success">4. Works! ✅</p>';
        echo '</div>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="box error">';
        echo '<h2>❌ ERROR</h2>';
        echo '<p style="font-size: 18px;">' . $e->getMessage() . '</p>';
        echo '</div>';
    }
    
} else {
    showForm();
}

function showForm() {
    ?>
    <div class="box">
        <h2>🔐 PASSWORD</h2>
        <form method="POST" style="text-align: center;">
            <p><input type="password" name="password" placeholder="fixnow123" required autofocus></p>
            <button type="submit" class="big-button">🔥 FIX NOW!</button>
        </form>
    </div>
    
    <div class="box">
        <h3>WILL DO:</h3>
        <div style="font-size: 18px; line-height: 2;">
            <p>🗑️ Clean old tables</p>
            <p>👥 Create users table</p>
            <p>🔧 Create versions table</p>
            <p>📝 Create logs table</p>
            <p>👤 Add 4 users (ali, test1, test2, hamza)</p>
            <p>🔢 Set version 5.0.0</p>
            <p>🔓 All users unbound</p>
        </div>
    </div>
    
    <div class="box" style="background: rgba(255,68,68,0.1);">
        <h3 class="error">⚠️ WARNING</h3>
        <p style="font-size: 18px;">This will DELETE ALL data!</p>
        <p style="font-size: 18px;">Fresh start only!</p>
    </div>
    <?php
}
?>
    </div>
</body>
</html>
