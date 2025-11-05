<?php
/**
 * ADD MISSING login_logs TABLE
 * Upload, run once, delete
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
    <title>Add Missing Table</title>
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
        .success { color: #0f0; font-size: 24px; }
        .error { color: #f00; font-size: 24px; }
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
    </style>
</head>
<body>
    <h1>🔧 ADD MISSING TABLE</h1>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($db->connect_error) {
                throw new Exception('Connection failed: ' . $db->connect_error);
            }
            
            echo '<div class="box">';
            
            // Create login_logs table
            $sql = "CREATE TABLE IF NOT EXISTS login_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) DEFAULT NULL,
                success TINYINT(1) DEFAULT 0,
                ip_address VARCHAR(50) DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_username (username),
                KEY idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if ($db->query($sql)) {
                echo '<p class="success">✅ login_logs table created!</p>';
            }
            
            // Verify
            $result = $db->query("SHOW TABLES LIKE 'login_logs'");
            if ($result->num_rows > 0) {
                echo '<p class="success">✅ Table exists and ready!</p>';
            }
            
            // Show all tables
            echo '<h3>All Tables:</h3>';
            $result = $db->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                echo "<p>✓ {$row[0]}</p>";
            }
            
            echo '<h2 class="success">🎉 FIXED!</h2>';
            echo '<p>Now refresh your panel - it will work!</p>';
            echo '<p class="error">DELETE this file from GitHub!</p>';
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
            <p style="font-size: 20px;">This will add the missing <strong>login_logs</strong> table</p>
            <form method="POST">
                <button type="submit">🔧 FIX NOW</button>
            </form>
        </div>
        <?php
    }
    ?>
</body>
</html>
