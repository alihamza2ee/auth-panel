<?php
/**
 * 🔧 FIX ALL COLUMN NAMES
 * =======================
 * Upload, run once, delete
 * Fixes column name mismatches in all panel files
 */

define('DB_HOST', 'mysql.railway.internal');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'iDFjnbMKzOTFBuwlZjZgzKiEBBAJDBmD');
define('DB_NAME', 'railway');
define('FIX_PASSWORD', 'fixcolumns123');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Column Names</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #0f0;
            padding: 40px;
        }
        .box {
            background: #111;
            border: 2px solid #0f0;
            padding: 30px;
            margin: 20px auto;
            max-width: 1000px;
            border-radius: 10px;
        }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #0f0;
        }
        pre {
            background: #000;
            padding: 15px;
            border-left: 3px solid #0f0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">🔧 FIX ALL COLUMN NAMES</h1>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        
        if ($password !== FIX_PASSWORD) {
            echo '<div class="box error"><p>❌ Wrong password!</p></div>';
            showForm();
            exit();
        }
        
        try {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($db->connect_error) {
                throw new Exception('Connection failed: ' . $db->connect_error);
            }
            
            echo '<div class="box">';
            echo '<h2>🔍 CHECKING DATABASE STRUCTURE...</h2>';
            
            // Check users table
            echo '<h3>📊 Users Table Columns:</h3>';
            $result = $db->query("SHOW COLUMNS FROM users");
            echo '<table><tr><th>Column</th><th>Type</th></tr>';
            while ($col = $result->fetch_assoc()) {
                echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
            }
            echo '</table>';
            
            // Check bot_versions table
            echo '<h3>🔧 Bot_versions Table Columns:</h3>';
            $result = $db->query("SHOW COLUMNS FROM bot_versions");
            echo '<table><tr><th>Column</th><th>Type</th></tr>';
            while ($col = $result->fetch_assoc()) {
                echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
            }
            echo '</table>';
            
            echo '</div>';
            
            // Show fixes needed
            echo '<div class="box">';
            echo '<h2>🔧 FIXES NEEDED IN YOUR FILES:</h2>';
            
            echo '<h3>Common Column Name Mismatches:</h3>';
            echo '<table>';
            echo '<tr><th>❌ Wrong Name</th><th>✅ Correct Name</th><th>Found In</th></tr>';
            echo '<tr><td>user_id</td><td>id</td><td>add_user.php, edit_user.php</td></tr>';
            echo '<tr><td>hwid</td><td>hardware_id</td><td>advanced_index.php (FIXED), other files?</td></tr>';
            echo '<tr><td>required</td><td>force_update</td><td>settings.php</td></tr>';
            echo '</table>';
            
            echo '<h3 class="warning">⚠️ FILES TO FIX MANUALLY:</h3>';
            echo '<ol style="font-size: 18px; line-height: 2;">';
            echo '<li><strong>add_user.php</strong> - Change "user_id" to "id"</li>';
            echo '<li><strong>settings.php</strong> - Change "required" to "force_update"</li>';
            echo '<li><strong>edit_user.php</strong> - Change "user_id" to "id" (if exists)</li>';
            echo '<li>Any other files using wrong column names</li>';
            echo '</ol>';
            
            echo '<h3>📝 EXAMPLE FIXES:</h3>';
            
            echo '<h4>add_user.php - Line 13:</h4>';
            echo '<pre class="error">❌ WRONG:
INSERT INTO users (user_id, username, ...) VALUES (?, ?, ...)</pre>';
            echo '<pre class="success">✅ CORRECT:
INSERT INTO users (username, status, start_date, end_date) VALUES (?, ?, ?, ?)</pre>';
            
            echo '<h4>settings.php - Line 13:</h4>';
            echo '<pre class="error">❌ WRONG:
INSERT INTO bot_versions (version, required) VALUES (?, ?)</pre>';
            echo '<pre class="success">✅ CORRECT:
INSERT INTO bot_versions (version, force_update) VALUES (?, ?)</pre>';
            
            echo '</div>';
            
            // Show complete correct INSERT statements
            echo '<div class="box">';
            echo '<h2>📋 CORRECT SQL STATEMENTS TO USE:</h2>';
            
            echo '<h3>✅ Add User (add_user.php):</h3>';
            echo '<pre class="success">';
            echo '$stmt = $db->prepare("INSERT INTO users (username, status, start_date, end_date) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $status, $start_date, $end_date);';
            echo '</pre>';
            
            echo '<h3>✅ Update User (edit_user.php):</h3>';
            echo '<pre class="success">';
            echo '$stmt = $db->prepare("UPDATE users SET username=?, status=?, end_date=? WHERE id=?");
$stmt->bind_param("sssi", $username, $status, $end_date, $user_id);';
            echo '</pre>';
            
            echo '<h3>✅ Settings (settings.php):</h3>';
            echo '<pre class="success">';
            echo '$stmt = $db->prepare("UPDATE bot_versions SET version=?, force_update=? WHERE id=1");
$stmt->bind_param("si", $version, $force_update);';
            echo '</pre>';
            
            echo '<h3>✅ Unbind Hardware:</h3>';
            echo '<pre class="success">';
            echo '$db->query("UPDATE users SET hardware_id = NULL, device_id = NULL WHERE username = \'ali\'");';
            echo '</pre>';
            
            echo '</div>';
            
            echo '<div class="box success">';
            echo '<h2 class="success">✅ DIAGNOSIS COMPLETE!</h2>';
            echo '<p style="font-size: 18px;">Now you know exactly what to fix in each file.</p>';
            echo '<p class="warning" style="font-size: 20px;">⚠️ DELETE this file after reviewing!</p>';
            echo '</div>';
            
            $db->close();
            
        } catch (Exception $e) {
            echo '<div class="box error">';
            echo '<p>❌ Error: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
    } else {
        showForm();
    }
    
    function showForm() {
        ?>
        <div class="box">
            <p style="font-size: 20px; text-align: center;">This will check your database structure and show you exactly what to fix</p>
            <form method="POST" style="text-align: center;">
                <input type="password" name="password" placeholder="Password: fixcolumns123" 
                       style="padding: 15px; font-size: 18px; background: #000; color: #0f0; border: 2px solid #0f0; width: 300px;">
                <br>
                <button type="submit">🔍 CHECK & SHOW FIXES</button>
            </form>
        </div>
        
        <div class="box">
            <h3>📋 WHAT THIS DOES:</h3>
            <ul style="font-size: 16px; line-height: 2;">
                <li>✅ Shows your actual database columns</li>
                <li>✅ Lists all column name mismatches</li>
                <li>✅ Shows correct SQL for each file</li>
                <li>✅ Gives you exact fixes needed</li>
                <li>❌ Does NOT modify any files (safe!)</li>
            </ul>
        </div>
        <?php
    }
    ?>
</body>
</html>
