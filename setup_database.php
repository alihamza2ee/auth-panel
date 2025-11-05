<?php
/**
 * DATABASE SETUP SCRIPT
 * ======================
 * Upload to GitHub, run ONCE in browser, then DELETE
 * 
 * URL: https://your-app.up.railway.app/setup_database.php
 */

header('Content-Type: text/html; charset=utf-8');

// ============================================
// DATABASE CONFIG - CHANGE THESE!
// ============================================
define('DB_HOST', 'autorack.proxy.rlwy.net');
define('DB_PORT', '12345');  // ← CHANGE THIS
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_PASSWORD');  // ← CHANGE THIS
define('DB_NAME', 'railway');

// Security check - change this password
define('SETUP_PASSWORD', 'setup123');  // ← CHANGE THIS!

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #0f0;
            padding: 20px;
            line-height: 1.6;
        }
        .box {
            background: #111;
            border: 2px solid #0f0;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
        }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        input, button {
            background: #222;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 10px 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 10px 5px;
        }
        button {
            cursor: pointer;
        }
        button:hover {
            background: #0f0;
            color: #000;
        }
        pre {
            background: #222;
            padding: 15px;
            border-left: 3px solid #0f0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🗄️ DATABASE SETUP SCRIPT</h1>
    
<?php

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password !== SETUP_PASSWORD) {
        echo '<div class="box error">❌ Wrong password!</div>';
        showForm();
        exit();
    }
    
    echo '<div class="box">';
    echo '<h2>🚀 Starting Setup...</h2>';
    
    try {
        // Connect to database
        echo '<p>📡 Connecting to database...</p>';
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($db->connect_error) {
            throw new Exception('Connection failed: ' . $db->connect_error);
        }
        
        echo '<p class="success">✅ Connected to database</p>';
        
        $db->set_charset('utf8mb4');
        
        // Create users table
        echo '<p>📋 Creating users table...</p>';
        $sql = "CREATE TABLE IF NOT EXISTS users (
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
            echo '<p class="success">✅ Users table created</p>';
        } else {
            throw new Exception('Users table error: ' . $db->error);
        }
        
        // Create bot_versions table
        echo '<p>📋 Creating bot_versions table...</p>';
        $sql = "CREATE TABLE IF NOT EXISTS bot_versions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            version VARCHAR(20) NOT NULL,
            force_update TINYINT(1) DEFAULT 0,
            download_url TEXT DEFAULT NULL,
            release_notes TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($db->query($sql)) {
            echo '<p class="success">✅ Bot_versions table created</p>';
        } else {
            throw new Exception('Bot_versions table error: ' . $db->error);
        }
        
        // Create activity_logs table
        echo '<p>📋 Creating activity_logs table...</p>';
        $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT DEFAULT NULL,
            ip_address VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($db->query($sql)) {
            echo '<p class="success">✅ Activity_logs table created</p>';
        } else {
            throw new Exception('Activity_logs table error: ' . $db->error);
        }
        
        // Add test user
        echo '<p>👤 Adding test user "ali"...</p>';
        $stmt = $db->prepare("INSERT IGNORE INTO users (username, status, start_date, end_date) VALUES (?, 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))");
        $username = 'ali';
        $stmt->bind_param("s", $username);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo '<p class="success">✅ User "ali" added (30 days)</p>';
            } else {
                echo '<p class="warning">⚠️ User "ali" already exists</p>';
            }
        }
        
        // Add test user 2
        echo '<p>👤 Adding test user "test1"...</p>';
        $stmt = $db->prepare("INSERT IGNORE INTO users (username, status, start_date, end_date) VALUES (?, 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY))");
        $username = 'test1';
        $stmt->bind_param("s", $username);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo '<p class="success">✅ User "test1" added (60 days)</p>';
            } else {
                echo '<p class="warning">⚠️ User "test1" already exists</p>';
            }
        }
        
        // Add default version
        echo '<p>🔧 Adding default bot version...</p>';
        $stmt = $db->prepare("INSERT IGNORE INTO bot_versions (version, force_update) VALUES ('5.0.0', 0)");
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo '<p class="success">✅ Version 5.0.0 added</p>';
            } else {
                echo '<p class="warning">⚠️ Version already exists</p>';
            }
        }
        
        // Show tables
        echo '<h3>📊 Verification:</h3>';
        echo '<pre>';
        
        $result = $db->query("SHOW TABLES");
        echo "Tables in database:\n";
        while ($row = $result->fetch_array()) {
            echo "  ✓ " . $row[0] . "\n";
        }
        
        echo "\nUsers:\n";
        $result = $db->query("SELECT id, username, status, start_date, end_date FROM users");
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['username']} ({$row['status']}) | {$row['start_date']} to {$row['end_date']}\n";
        }
        
        echo "\nBot Versions:\n";
        $result = $db->query("SELECT version, force_update FROM bot_versions");
        while ($row = $result->fetch_assoc()) {
            echo "  - Version {$row['version']} (Force: " . ($row['force_update'] ? 'Yes' : 'No') . ")\n";
        }
        
        echo '</pre>';
        
        echo '<h2 class="success">🎉 SETUP COMPLETE!</h2>';
        echo '<p>✅ All tables created</p>';
        echo '<p>✅ Test users added</p>';
        echo '<p>✅ Default version set</p>';
        echo '<p class="warning">⚠️ IMPORTANT: DELETE this file (setup_database.php) from GitHub now!</p>';
        echo '<p>Now test your bot!</p>';
        
        $db->close();
        
    } catch (Exception $e) {
        echo '<p class="error">❌ ERROR: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
} else {
    showForm();
}

function showForm() {
    ?>
    <div class="box">
        <h2>🔐 Enter Setup Password</h2>
        <form method="POST">
            <p>Password: <input type="password" name="password" placeholder="setup123" required></p>
            <button type="submit">🚀 Run Setup</button>
        </form>
        
        <h3>📋 Instructions:</h3>
        <ol>
            <li>Edit lines 10-12 in this file (database credentials)</li>
            <li>Edit line 15 (change setup password)</li>
            <li>Upload to GitHub</li>
            <li>Wait 2-3 min for Railway deploy</li>
            <li>Visit: https://your-app.up.railway.app/setup_database.php</li>
            <li>Enter setup password</li>
            <li>Click "Run Setup"</li>
            <li>DELETE this file after setup!</li>
        </ol>
        
        <h3>⚠️ Security:</h3>
        <p>Change SETUP_PASSWORD on line 15 before uploading!</p>
        <p>Delete this file immediately after running setup!</p>
    </div>
    <?php
}
?>

</body>
</html>
