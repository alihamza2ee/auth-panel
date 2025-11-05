<?php
/**
 * 🔓 MAKE PASSWORD OPTIONAL FOR ALL USERS
 * ========================================
 * Upload to GitHub, run once, delete
 * Makes password NULL for all users so bot can login with just username
 */

define('DB_HOST', 'mysql.railway.internal');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'iDFjnbMKzOTFBuwlZjZgzKiEBBAJDBmD');
define('DB_NAME', 'railway');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Make Password Optional</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #0f0;
            padding: 40px;
            text-align: center;
        }
        .box {
            background: #111;
            border: 2px solid #0f0;
            padding: 30px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 10px;
        }
        .success { color: #0f0; font-size: 20px; }
        .error { color: #f00; font-size: 20px; }
        .warning { color: #ff0; font-size: 18px; }
        button {
            background: #0f0;
            color: #000;
            border: none;
            padding: 20px 40px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            margin: 20px;
        }
        button:hover {
            background: #0c0;
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
    </style>
</head>
<body>
    <h1>🔓 MAKE PASSWORD OPTIONAL</h1>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($db->connect_error) {
                throw new Exception('Connection failed: ' . $db->connect_error);
            }
            
            echo '<div class="box">';
            
            // Show current status
            echo '<h2>📊 BEFORE:</h2>';
            echo '<table>';
            echo '<tr><th>Username</th><th>Password Status</th></tr>';
            
            $result = $db->query("SELECT username, password FROM users");
            $users_with_password = 0;
            $users_without_password = 0;
            
            while ($row = $result->fetch_assoc()) {
                $has_password = !empty($row['password']);
                if ($has_password) {
                    $users_with_password++;
                    $status = '<span style="color: #ff0;">HAS PASSWORD</span>';
                } else {
                    $users_without_password++;
                    $status = '<span style="color: #0f0;">NO PASSWORD</span>';
                }
                echo "<tr><td>{$row['username']}</td><td>{$status}</td></tr>";
            }
            echo '</table>';
            
            echo "<p class='warning'>Users with password: {$users_with_password}</p>";
            echo "<p class='success'>Users without password: {$users_without_password}</p>";
            
            // Make all passwords NULL
            echo '<h2>🔄 UPDATING...</h2>';
            $db->query("UPDATE users SET password = NULL");
            $updated = $db->affected_rows;
            
            echo "<p class='success'>✅ Updated {$updated} users</p>";
            
            // Show after status
            echo '<h2>📊 AFTER:</h2>';
            echo '<table>';
            echo '<tr><th>Username</th><th>Password Status</th></tr>';
            
            $result = $db->query("SELECT username, password FROM users");
            while ($row = $result->fetch_assoc()) {
                $has_password = !empty($row['password']);
                $status = $has_password ? '<span style="color: #ff0;">HAS PASSWORD</span>' : '<span style="color: #0f0;">NO PASSWORD ✓</span>';
                echo "<tr><td>{$row['username']}</td><td>{$status}</td></tr>";
            }
            echo '</table>';
            
            echo '<h2 class="success">🎉 DONE!</h2>';
            echo '<p class="success">All users can now login with just username!</p>';
            echo '<p class="success">Bot will work without password!</p>';
            echo '<p class="error">⚠️ DELETE this file from GitHub NOW!</p>';
            echo '</div>';
            
            $db->close();
            
        } catch (Exception $e) {
            echo '<div class="box error">';
            echo '<p>❌ Error: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
    } else {
        ?>
        <div class="box">
            <h2>🔓 REMOVE ALL PASSWORDS</h2>
            <p style="font-size: 18px; line-height: 2;">
                This will set password = NULL for ALL users<br>
                Bot can login with just username!<br>
                No password needed!
            </p>
            <form method="POST">
                <button type="submit">🔓 MAKE PASSWORD OPTIONAL</button>
            </form>
        </div>
        
        <div class="box">
            <h3>📝 WHAT THIS DOES:</h3>
            <ul style="text-align: left; font-size: 16px; line-height: 2;">
                <li>✅ Shows current password status</li>
                <li>✅ Sets password = NULL for ALL users</li>
                <li>✅ Shows updated status</li>
                <li>✅ Bot can login with username only!</li>
            </ul>
        </div>
        
        <div class="box">
            <h3>⚡ QUICK STEPS:</h3>
            <ol style="text-align: left; font-size: 16px; line-height: 2;">
                <li>Upload this file to GitHub</li>
                <li>Wait 2 minutes</li>
                <li>Open: your-app.up.railway.app/make_password_optional.php</li>
                <li>Click button</li>
                <li>See results</li>
                <li>DELETE file from GitHub</li>
                <li>Run bot → Works! ✅</li>
            </ol>
        </div>
        <?php
    }
    ?>
</body>
</html>
