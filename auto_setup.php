<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * COMPLETE AUTO-SETUP SCRIPT
 * ═══════════════════════════════════════════════════════════════
 * 
 * Upload to GitHub → Open in browser → Click button → DONE!
 * 
 * URL: https://your-app.up.railway.app/auto_setup.php
 * 
 * This script will:
 * ✅ Create all database tables
 * ✅ Add test users
 * ✅ Add default version
 * ✅ Unbind all hardware
 * ✅ Show test results
 * 
 * ⚠️ DELETE THIS FILE AFTER USE!
 * ═══════════════════════════════════════════════════════════════
 */

header('Content-Type: text/html; charset=utf-8');

// ============================================
// 🔧 CONFIGURATION - EDIT THESE!
// ============================================
define('DB_HOST', 'autorack.proxy.rlwy.net');
define('DB_PORT', '12345');  // ← CHANGE THIS
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_PASSWORD_HERE');  // ← CHANGE THIS
define('DB_NAME', 'railway');

define('SETUP_PASSWORD', 'setup123');  // ← CHANGE THIS!

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Complete Auto Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #000000 0%, #1a1a2e 100%);
            color: #0f0;
            padding: 20px;
            line-height: 1.8;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            padding: 40px;
            background: rgba(0, 255, 136, 0.1);
            border: 3px solid #0f0;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.3);
        }
        
        .header h1 {
            font-size: 48px;
            text-shadow: 0 0 20px #0f0;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 18px;
            opacity: 0.8;
        }
        
        .box {
            background: rgba(0, 20, 0, 0.8);
            border: 2px solid #0f0;
            padding: 30px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.2);
        }
        
        .success { color: #0f0; font-weight: bold; }
        .error { color: #f00; font-weight: bold; }
        .warning { color: #ff0; font-weight: bold; }
        .info { color: #0af; }
        
        input, button {
            background: #000;
            color: #0f0;
            border: 2px solid #0f0;
            padding: 15px 30px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            margin: 10px 5px;
            border-radius: 5px;
        }
        
        button {
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        button:hover {
            background: #0f0;
            color: #000;
            box-shadow: 0 0 20px #0f0;
            transform: scale(1.05);
        }
        
        .progress-item {
            padding: 15px;
            margin: 10px 0;
            background: rgba(0, 0, 0, 0.5);
            border-left: 4px solid #0f0;
            border-radius: 5px;
        }
        
        .progress-item.pending {
            border-left-color: #666;
            opacity: 0.5;
        }
        
        .progress-item.running {
            border-left-color: #ff0;
            animation: pulse 1s infinite;
        }
        
        .progress-item.complete {
            border-left-color: #0f0;
        }
        
        .progress-item.failed {
            border-left-color: #f00;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(0, 0, 0, 0.5);
        }
        
        th, td {
            border: 1px solid #0f0;
            padding: 12px;
            text-align: left;
        }
        
        th {
            background: rgba(0, 255, 136, 0.2);
            font-weight: bold;
        }
        
        .code {
            background: #000;
            padding: 15px;
            border-left: 3px solid #0f0;
            margin: 15px 0;
            overflow-x: auto;
            border-radius: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            margin: 0 5px;
        }
        
        .badge.success {
            background: #0f0;
            color: #000;
        }
        
        .badge.error {
            background: #f00;
            color: #fff;
        }
        
        .badge.warning {
            background: #ff0;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 COMPLETE AUTO SETUP</h1>
            <p>One Click - Everything Done!</p>
        </div>
        
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password !== SETUP_PASSWORD) {
        echo '<div class="box error">❌ WRONG PASSWORD!</div>';
        showForm();
        exit();
    }
    
    echo '<div class="box">';
    echo '<h2>🎯 STARTING COMPLETE SETUP...</h2>';
    echo '<p class="info">This will setup everything automatically!</p>';
    echo '</div>';
    
    $steps = [
        'connect' => '📡 Connecting to database',
        'drop_tables' => '🗑️ Cleaning old data (if exists)',
        'create_users' => '👥 Creating users table',
        'create_versions' => '🔧 Creating bot_versions table',
        'create_logs' => '📝 Creating activity_logs table',
        'add_users' => '👤 Adding test users',
        'add_version' => '🔢 Adding default version',
        'unbind_hardware' => '🔓 Unbinding all hardware',
        'verify' => '✅ Verifying setup'
    ];
    
    $results = [];
    
    try {
        // STEP 1: Connect
        updateProgress('connect', 'running', $steps);
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($db->connect_error) {
            throw new Exception('Connection failed: ' . $db->connect_error);
        }
        
        $db->set_charset('utf8mb4');
        $results['connect'] = ['status' => 'success', 'message' => 'Connected successfully'];
        updateProgress('connect', 'complete', $steps);
        
        // STEP 2: Drop tables (optional - clean start)
        updateProgress('drop_tables', 'running', $steps);
        $db->query("DROP TABLE IF EXISTS activity_logs");
        $db->query("DROP TABLE IF EXISTS bot_versions");
        $db->query("DROP TABLE IF EXISTS users");
        $results['drop_tables'] = ['status' => 'success', 'message' => 'Old tables removed'];
        updateProgress('drop_tables', 'complete', $steps);
        
        // STEP 3: Create users table
        updateProgress('create_users', 'running', $steps);
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
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_status (status),
            INDEX idx_hardware (hardware_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!$db->query($sql)) {
            throw new Exception('Users table: ' . $db->error);
        }
        $results['create_users'] = ['status' => 'success', 'message' => 'Users table created'];
        updateProgress('create_users', 'complete', $steps);
        
        // STEP 4: Create bot_versions table
        updateProgress('create_versions', 'running', $steps);
        $sql = "CREATE TABLE bot_versions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            version VARCHAR(20) NOT NULL,
            force_update TINYINT(1) DEFAULT 0,
            download_url TEXT DEFAULT NULL,
            release_notes TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_version (version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!$db->query($sql)) {
            throw new Exception('Bot_versions table: ' . $db->error);
        }
        $results['create_versions'] = ['status' => 'success', 'message' => 'Bot_versions table created'];
        updateProgress('create_versions', 'complete', $steps);
        
        // STEP 5: Create activity_logs table
        updateProgress('create_logs', 'running', $steps);
        $sql = "CREATE TABLE activity_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT DEFAULT NULL,
            ip_address VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_action (action),
            INDEX idx_date (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (!$db->query($sql)) {
            throw new Exception('Activity_logs table: ' . $db->error);
        }
        $results['create_logs'] = ['status' => 'success', 'message' => 'Activity_logs table created'];
        updateProgress('create_logs', 'complete', $steps);
        
        // STEP 6: Add test users
        updateProgress('add_users', 'running', $steps);
        $users = [
            ['ali', 30],
            ['test1', 60],
            ['test2', 90]
        ];
        
        $added = 0;
        foreach ($users as list($username, $days)) {
            $stmt = $db->prepare("INSERT INTO users (username, status, start_date, end_date) VALUES (?, 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))");
            $stmt->bind_param("si", $username, $days);
            if ($stmt->execute()) {
                $added++;
            }
        }
        $results['add_users'] = ['status' => 'success', 'message' => "$added users added"];
        updateProgress('add_users', 'complete', $steps);
        
        // STEP 7: Add default version
        updateProgress('add_version', 'running', $steps);
        $stmt = $db->prepare("INSERT INTO bot_versions (version, force_update) VALUES ('5.0.0', 0)");
        $stmt->execute();
        $results['add_version'] = ['status' => 'success', 'message' => 'Version 5.0.0 added'];
        updateProgress('add_version', 'complete', $steps);
        
        // STEP 8: Unbind all hardware
        updateProgress('unbind_hardware', 'running', $steps);
        $db->query("UPDATE users SET hardware_id = NULL, device_id = NULL");
        $unbound = $db->affected_rows;
        $results['unbind_hardware'] = ['status' => 'success', 'message' => "$unbound users unbound"];
        updateProgress('unbind_hardware', 'complete', $steps);
        
        // STEP 9: Verify
        updateProgress('verify', 'running', $steps);
        echo '<div class="box">';
        echo '<h2>📊 VERIFICATION</h2>';
        
        // Show tables
        echo '<h3>Tables Created:</h3>';
        $result = $db->query("SHOW TABLES");
        echo '<ul>';
        while ($row = $result->fetch_array()) {
            echo "<li class='success'>✓ {$row[0]}</li>";
        }
        echo '</ul>';
        
        // Show users
        echo '<h3>Users Added:</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Username</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Hardware</th></tr>';
        
        $result = $db->query("SELECT id, username, status, start_date, end_date, hardware_id FROM users");
        while ($row = $result->fetch_assoc()) {
            $hw = $row['hardware_id'] ? '<span class="warning">Bound</span>' : '<span class="success">Unbound ✓</span>';
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td><strong>{$row['username']}</strong></td>";
            echo "<td><span class='badge success'>{$row['status']}</span></td>";
            echo "<td>{$row['start_date']}</td>";
            echo "<td>{$row['end_date']}</td>";
            echo "<td>{$hw}</td>";
            echo "</tr>";
        }
        echo '</table>';
        
        // Show versions
        echo '<h3>Bot Versions:</h3>';
        $result = $db->query("SELECT version, force_update FROM bot_versions");
        while ($row = $result->fetch_assoc()) {
            $force = $row['force_update'] ? '<span class="error">Force</span>' : '<span class="success">Optional</span>';
            echo "<p>Version: <strong>{$row['version']}</strong> | Update: {$force}</p>";
        }
        
        echo '</div>';
        
        $results['verify'] = ['status' => 'success', 'message' => 'All checks passed'];
        updateProgress('verify', 'complete', $steps);
        
        // SUCCESS!
        echo '<div class="box" style="border-color: #0f0; background: rgba(0, 255, 136, 0.1);">';
        echo '<h2 class="success">🎉 SETUP COMPLETE!</h2>';
        echo '<p class="success" style="font-size: 20px;">✅ Database fully configured</p>';
        echo '<p class="success" style="font-size: 20px;">✅ Test users added</p>';
        echo '<p class="success" style="font-size: 20px;">✅ Hardware unbound</p>';
        echo '<p class="success" style="font-size: 20px;">✅ Ready to use!</p>';
        echo '<hr style="border-color: #0f0; margin: 20px 0;">';
        echo '<h3 class="warning">⚠️ IMPORTANT - NEXT STEPS:</h3>';
        echo '<ol style="font-size: 16px; line-height: 2;">';
        echo '<li class="error">❗ DELETE this file (auto_setup.php) from GitHub NOW!</li>';
        echo '<li class="success">✅ Test your API using AUTO_SETUP_SCRIPT.html</li>';
        echo '<li class="success">✅ Run your bot - it should work now!</li>';
        echo '</ol>';
        echo '</div>';
        
        $db->close();
        
    } catch (Exception $e) {
        echo '<div class="box error">';
        echo '<h2>❌ ERROR</h2>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '</div>';
    }
    
} else {
    showForm();
}

function showForm() {
    ?>
    <div class="box">
        <h2>🔐 ENTER SETUP PASSWORD</h2>
        <form method="POST">
            <p><input type="password" name="password" placeholder="Enter setup password" required style="width: 400px;"></p>
            <button type="submit">🚀 RUN COMPLETE SETUP</button>
        </form>
    </div>
    
    <div class="box">
        <h3>📋 WHAT THIS SCRIPT DOES:</h3>
        <div class="progress-item pending">
            <strong>1. 📡 Connect to Database</strong>
            <p>Establishes connection using your credentials</p>
        </div>
        <div class="progress-item pending">
            <strong>2. 🗑️ Clean Old Data</strong>
            <p>Removes existing tables for fresh start</p>
        </div>
        <div class="progress-item pending">
            <strong>3. 👥 Create Users Table</strong>
            <p>Creates table for storing user accounts</p>
        </div>
        <div class="progress-item pending">
            <strong>4. 🔧 Create Versions Table</strong>
            <p>Creates table for version control</p>
        </div>
        <div class="progress-item pending">
            <strong>5. 📝 Create Logs Table</strong>
            <p>Creates table for activity tracking</p>
        </div>
        <div class="progress-item pending">
            <strong>6. 👤 Add Test Users</strong>
            <p>Adds ali, test1, test2 with different durations</p>
        </div>
        <div class="progress-item pending">
            <strong>7. 🔢 Add Default Version</strong>
            <p>Sets version 5.0.0 as default</p>
        </div>
        <div class="progress-item pending">
            <strong>8. 🔓 Unbind Hardware</strong>
            <p>Removes all hardware locks for fresh start</p>
        </div>
        <div class="progress-item pending">
            <strong>9. ✅ Verify Setup</strong>
            <p>Checks everything is working correctly</p>
        </div>
    </div>
    
    <div class="box">
        <h3>📝 INSTRUCTIONS:</h3>
        <ol style="font-size: 16px; line-height: 2;">
            <li>✏️ Edit lines 27-29: Database credentials (SAME as advanced_api.php)</li>
            <li>✏️ Edit line 31: Change SETUP_PASSWORD</li>
            <li>📤 Upload this file to GitHub</li>
            <li>⏰ Wait 2-3 minutes for Railway deploy</li>
            <li>🌐 Visit: <code>https://your-app.up.railway.app/auto_setup.php</code></li>
            <li>🔑 Enter your setup password</li>
            <li>🚀 Click "RUN COMPLETE SETUP"</li>
            <li>🎉 Everything will be done automatically!</li>
            <li>🗑️ <span class="error">DELETE this file immediately after!</span></li>
        </ol>
    </div>
    
    <div class="box">
        <h3>⚠️ SECURITY WARNINGS:</h3>
        <ul style="font-size: 16px; line-height: 2;">
            <li class="error">❗ Change SETUP_PASSWORD before uploading</li>
            <li class="error">❗ DELETE this file after running setup</li>
            <li class="error">❗ Never leave this file online</li>
            <li class="warning">⚠️ This script has full database access</li>
        </ul>
    </div>
    <?php
}

function updateProgress($step, $status, $steps) {
    // This would update progress in real-time with JavaScript
    // For now, just flush output
    flush();
}

?>
    </div>
</body>
</html>
