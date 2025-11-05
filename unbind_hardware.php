<?php
/**
 * UNBIND HARDWARE SCRIPT
 * =======================
 * Upload to GitHub, run in browser, then DELETE
 * 
 * URL: https://your-app.up.railway.app/unbind_hardware.php
 */

header('Content-Type: text/html; charset=utf-8');

// ============================================
// DATABASE CONFIG - SAME AS advanced_api.php
// ============================================
define('DB_HOST', 'autorack.proxy.rlwy.net');
define('DB_PORT', '12345');  // ← CHANGE THIS
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_PASSWORD');  // ← CHANGE THIS
define('DB_NAME', 'railway');

// Security password
define('UNBIND_PASSWORD', 'unbind123');  // ← CHANGE THIS!

?>
<!DOCTYPE html>
<html>
<head>
    <title>Unbind Hardware</title>
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
        input, button, select {
            background: #222;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 10px 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 10px 5px;
            width: 300px;
        }
        button {
            cursor: pointer;
            width: auto;
        }
        button:hover {
            background: #0f0;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #0f0;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #222;
        }
    </style>
</head>
<body>
    <h1>🔓 UNBIND HARDWARE</h1>
    
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if ($password !== UNBIND_PASSWORD) {
        echo '<div class="box error">❌ Wrong password!</div>';
        showForm();
        exit();
    }
    
    echo '<div class="box">';
    
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($db->connect_error) {
            throw new Exception('Connection failed: ' . $db->connect_error);
        }
        
        $db->set_charset('utf8mb4');
        
        if ($action === 'unbind_all') {
            // Unbind all users
            echo '<h2>🔓 Unbinding ALL users...</h2>';
            $result = $db->query("UPDATE users SET hardware_id = NULL, device_id = NULL");
            
            if ($result) {
                $affected = $db->affected_rows;
                echo "<p class='success'>✅ Unbound {$affected} users</p>";
            }
            
        } elseif ($action === 'unbind_user') {
            // Unbind specific user
            $username = $_POST['username'] ?? '';
            
            if (empty($username)) {
                echo "<p class='error'>❌ Username required</p>";
            } else {
                echo "<h2>🔓 Unbinding user: {$username}...</h2>";
                $stmt = $db->prepare("UPDATE users SET hardware_id = NULL, device_id = NULL WHERE username = ?");
                $stmt->bind_param("s", $username);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        echo "<p class='success'>✅ User '{$username}' unbound successfully</p>";
                    } else {
                        echo "<p class='warning'>⚠️ User '{$username}' not found or already unbound</p>";
                    }
                }
            }
        }
        
        // Show current status
        echo '<h3>📊 Current Users Status:</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Username</th><th>Status</th><th>Hardware ID</th><th>Device ID</th><th>Last Login</th></tr>';
        
        $result = $db->query("SELECT id, username, status, hardware_id, device_id, last_login FROM users ORDER BY id");
        
        while ($row = $result->fetch_assoc()) {
            $hw = $row['hardware_id'] ?: '<span class="success">None (Unbound)</span>';
            $dev = $row['device_id'] ?: '<span class="success">None</span>';
            $login = $row['last_login'] ?: 'Never';
            
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$hw}</td>";
            echo "<td>{$dev}</td>";
            echo "<td>{$login}</td>";
            echo "</tr>";
        }
        
        echo '</table>';
        
        echo '<p class="warning">⚠️ IMPORTANT: DELETE this file (unbind_hardware.php) from GitHub now!</p>';
        
        $db->close();
        
    } catch (Exception $e) {
        echo '<p class="error">❌ ERROR: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
    echo '<div class="box">';
    echo '<h3>🔄 Unbind More?</h3>';
    showForm();
    echo '</div>';
    
} else {
    showForm();
}

function showForm() {
    ?>
    <div class="box">
        <h2>🔐 Enter Password</h2>
        
        <form method="POST">
            <h3>Option 1: Unbind Specific User</h3>
            <p>Password: <input type="password" name="password" placeholder="unbind123" required></p>
            <p>Username: <input type="text" name="username" placeholder="ali"></p>
            <button type="submit" name="action" value="unbind_user">🔓 Unbind This User</button>
        </form>
        
        <hr style="border-color: #0f0; margin: 30px 0;">
        
        <form method="POST">
            <h3>Option 2: Unbind ALL Users</h3>
            <p>Password: <input type="password" name="password" placeholder="unbind123" required></p>
            <button type="submit" name="action" value="unbind_all">🔓 Unbind ALL Users</button>
        </form>
        
        <h3>📋 Instructions:</h3>
        <ol>
            <li>Edit lines 12-14 (database credentials - same as advanced_api.php)</li>
            <li>Edit line 17 (change unbind password)</li>
            <li>Upload to GitHub</li>
            <li>Wait 2-3 min for Railway deploy</li>
            <li>Visit: https://your-app.up.railway.app/unbind_hardware.php</li>
            <li>Choose option & enter password</li>
            <li>Click button</li>
            <li>DELETE this file after use!</li>
        </ol>
        
        <h3>⚠️ Security:</h3>
        <p class="warning">Change UNBIND_PASSWORD on line 17 before uploading!</p>
        <p class="warning">Delete this file immediately after unbinding!</p>
    </div>
    <?php
}
?>

</body>
</html>
