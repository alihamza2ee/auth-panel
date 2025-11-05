<?php
/**
 * TEST DATABASE CONNECTION
 * =========================
 * Upload to GitHub, open in browser to test connection
 * URL: https://your-app.up.railway.app/test_db.php
 */

header('Content-Type: text/html; charset=utf-8');

// ============================================
// DATABASE CONFIG - EDIT THESE!
// ============================================
define('DB_HOST', 'autorack.proxy.rlwy.net');
define('DB_PORT', '12345');  // ← CHANGE THIS
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_PASSWORD');  // ← CHANGE THIS
define('DB_NAME', 'railway');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Database Connection</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #0f0;
            padding: 20px;
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
        pre {
            background: #000;
            padding: 15px;
            border-left: 3px solid #0f0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🔍 DATABASE CONNECTION TEST</h1>
    
    <div class="box">
        <h2>📋 Configuration</h2>
        <pre>
Host: <?php echo DB_HOST; ?>
Port: <?php echo DB_PORT; ?>
User: <?php echo DB_USER; ?>
Pass: <?php echo str_repeat('*', strlen(DB_PASS)); ?>
Database: <?php echo DB_NAME; ?>
</pre>
    </div>
    
    <div class="box">
        <h2>🧪 Testing Connection...</h2>
        
        <?php
        try {
            echo "<p>📡 Attempting connection...</p>";
            
            // Try connection
            $start = microtime(true);
            $db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            $time = round((microtime(true) - $start) * 1000, 2);
            
            if ($db->connect_error) {
                throw new Exception($db->connect_error);
            }
            
            echo "<p class='success'>✅ Connected successfully! ({$time}ms)</p>";
            echo "<p class='success'>MySQL Version: " . $db->server_info . "</p>";
            
            // Test query
            echo "<p>🔍 Testing query...</p>";
            $result = $db->query("SELECT DATABASE() as db, NOW() as time");
            $row = $result->fetch_assoc();
            
            echo "<p class='success'>✅ Database: {$row['db']}</p>";
            echo "<p class='success'>✅ Server Time: {$row['time']}</p>";
            
            // Check tables
            echo "<p>📊 Checking existing tables...</p>";
            $result = $db->query("SHOW TABLES");
            
            if ($result->num_rows > 0) {
                echo "<p class='success'>✅ Found {$result->num_rows} tables:</p>";
                echo "<ul>";
                while ($row = $result->fetch_array()) {
                    echo "<li>{$row[0]}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='warning'>⚠️ No tables found (database is empty)</p>";
            }
            
            $db->close();
            
            echo "<div style='margin-top: 30px; padding: 20px; background: rgba(0,255,136,0.1); border: 2px solid #0f0;'>";
            echo "<h3 class='success'>🎉 CONNECTION SUCCESSFUL!</h3>";
            echo "<p>Your database credentials are correct.</p>";
            echo "<p>You can now run auto_setup.php</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ CONNECTION FAILED!</p>";
            echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
            
            echo "<div style='margin-top: 30px; padding: 20px; background: rgba(255,0,0,0.1); border: 2px solid #f00;'>";
            echo "<h3 class='error'>🔴 FIX THESE ISSUES:</h3>";
            echo "<ol style='font-size: 16px; line-height: 2;'>";
            
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                echo "<li class='error'>❌ Wrong password or username</li>";
                echo "<li>Check: Railway → MySQL → Connect tab</li>";
                echo "<li>Copy correct MYSQLPASSWORD</li>";
                echo "<li>Update line 14 in this file</li>";
            } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                echo "<li class='error'>❌ Database name wrong</li>";
                echo "<li>Usually it's 'railway'</li>";
                echo "<li>Check: Railway → MySQL → Connect tab</li>";
            } elseif (strpos($e->getMessage(), 'timed out') !== false || strpos($e->getMessage(), 'gone away') !== false) {
                echo "<li class='error'>❌ Connection timeout</li>";
                echo "<li>Check: Railway MySQL is running</li>";
                echo "<li>Check: Host and Port are correct</li>";
                echo "<li>Try again in a few seconds</li>";
            } else {
                echo "<li class='error'>❌ Unknown error</li>";
                echo "<li>Check: All credentials are correct</li>";
                echo "<li>Check: Railway MySQL service is running</li>";
            }
            
            echo "</ol>";
            echo "</div>";
        }
        ?>
    </div>
    
    <div class="box">
        <h3>📝 How to Get Correct Credentials:</h3>
        <ol style="font-size: 16px; line-height: 2;">
            <li>Open Railway Dashboard</li>
            <li>Click MySQL service</li>
            <li>Click "Connect" tab</li>
            <li>Copy these values:
                <ul>
                    <li>MYSQLHOST → DB_HOST</li>
                    <li>MYSQLPORT → DB_PORT</li>
                    <li>MYSQLUSER → DB_USER (usually 'root')</li>
                    <li>MYSQLPASSWORD → DB_PASS</li>
                    <li>MYSQLDATABASE → DB_NAME (usually 'railway')</li>
                </ul>
            </li>
            <li>Edit lines 12-16 in this file</li>
            <li>Upload again to GitHub</li>
            <li>Wait 2-3 minutes</li>
            <li>Refresh this page</li>
        </ol>
    </div>
    
    <div class="box">
        <h3>⚠️ Common Issues:</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #0f0;">
                <th style="text-align: left; padding: 10px;">Error</th>
                <th style="text-align: left; padding: 10px;">Fix</th>
            </tr>
            <tr>
                <td style="padding: 10px;">Access denied</td>
                <td style="padding: 10px;">Wrong password - copy exact password from Railway</td>
            </tr>
            <tr>
                <td style="padding: 10px;">Unknown database</td>
                <td style="padding: 10px;">Database name wrong - usually 'railway'</td>
            </tr>
            <tr>
                <td style="padding: 10px;">Connection timed out</td>
                <td style="padding: 10px;">Wrong host or port - check Railway Connect tab</td>
            </tr>
            <tr>
                <td style="padding: 10px;">MySQL gone away</td>
                <td style="padding: 10px;">MySQL service down or restarting - wait and retry</td>
            </tr>
        </table>
    </div>
    
    <div class="box">
        <p class="warning">⚠️ DELETE this file after testing!</p>
    </div>
</body>
</html>
