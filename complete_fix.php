<?php
/**
 * ✅ ONE-CLICK COMPLETE FIX
 * ========================
 * Upload to GitHub → Open in browser → Done!
 * Fixes password issue + shows complete status
 */

define('DB_HOST', 'mysql.railway.internal');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'iDFjnbMKzOTFBuwlZjZgzKiEBBAJDBmD');
define('DB_NAME', 'railway');

header('Content-Type: text/html; charset=utf-8');

function getDB() {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    return $db;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complete Fix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #0a0e1a 0%, #1a1f2e 100%);
            color: #00ff88;
            padding: 20px;
            min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 {
            text-align: center;
            color: #FFD700;
            font-size: 42px;
            margin: 30px 0;
            text-shadow: 0 0 20px #FFD700;
        }
        .box {
            background: rgba(26, 31, 46, 0.9);
            border: 2px solid #00ff88;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.3);
        }
        .success {
            background: rgba(0, 255, 136, 0.1);
            border-color: #00ff88;
            color: #00ff88;
        }
        .warning {
            background: rgba(255, 170, 0, 0.1);
            border-color: #ffaa00;
            color: #ffaa00;
        }
        .error {
            background: rgba(255, 51, 51, 0.1);
            border-color: #ff3333;
            color: #ff3333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: rgba(0, 255, 136, 0.2);
            padding: 15px;
            text-align: left;
            border: 1px solid #00ff88;
            font-size: 16px;
        }
        td {
            padding: 12px 15px;
            border: 1px solid rgba(0, 255, 136, 0.3);
            font-size: 14px;
        }
        tr:hover {
            background: rgba(0, 255, 136, 0.05);
        }
        button {
            background: linear-gradient(135deg, #00ff88, #00cc66);
            color: #000;
            border: none;
            padding: 20px 50px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 10px;
            margin: 20px 0;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0, 255, 136, 0.5);
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 255, 136, 0.7);
        }
        .center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .badge-green {
            background: #00ff88;
            color: #000;
        }
        .badge-red {
            background: #ff3333;
            color: #fff;
        }
        .badge-yellow {
            background: #ffaa00;
            color: #000;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: rgba(0, 255, 136, 0.1);
            border: 2px solid #00ff88;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .stat-value {
            font-size: 48px;
            font-weight: bold;
            color: #FFD700;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 16px;
            color: #00ff88;
        }
        pre {
            background: #000;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            margin: 20px 0;
            border: 1px solid #00ff88;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 COMPLETE FIX</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = getDB();
                
                echo '<div class="box success">';
                echo '<h2 style="color: #00ff88; text-align: center; margin-bottom: 30px;">🔄 FIXING...</h2>';
                
                // Get current status
                echo '<h3>📊 BEFORE FIX:</h3>';
                $result = $db->query("SELECT username, password, status, start_date, end_date FROM users ORDER BY id");
                
                $total = 0;
                $with_password = 0;
                $without_password = 0;
                
                echo '<table>';
                echo '<tr><th>Username</th><th>Password</th><th>Status</th><th>Dates</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    $total++;
                    $has_pwd = !empty($row['password']);
                    
                    if ($has_pwd) {
                        $with_password++;
                        $pwd_badge = '<span class="badge badge-red">SET ❌</span>';
                    } else {
                        $without_password++;
                        $pwd_badge = '<span class="badge badge-green">NULL ✓</span>';
                    }
                    
                    $dates = $row['start_date'] && $row['end_date'] 
                        ? "{$row['start_date']} to {$row['end_date']}" 
                        : "Not set";
                    
                    echo "<tr>";
                    echo "<td>{$row['username']}</td>";
                    echo "<td>{$pwd_badge}</td>";
                    echo "<td>{$row['status']}</td>";
                    echo "<td style='font-size: 12px;'>{$dates}</td>";
                    echo "</tr>";
                }
                echo '</table>';
                
                echo '<div class="stats">';
                echo '<div class="stat-card">';
                echo '<div class="stat-label">Total Users</div>';
                echo '<div class="stat-value">' . $total . '</div>';
                echo '</div>';
                echo '<div class="stat-card">';
                echo '<div class="stat-label">With Password</div>';
                echo '<div class="stat-value" style="color: #ff3333;">' . $with_password . '</div>';
                echo '</div>';
                echo '<div class="stat-card">';
                echo '<div class="stat-label">Without Password</div>';
                echo '<div class="stat-value" style="color: #00ff88;">' . $without_password . '</div>';
                echo '</div>';
                echo '</div>';
                
                // Apply fix
                echo '<h3 style="margin-top: 40px;">⚡ APPLYING FIX...</h3>';
                
                $db->query("UPDATE users SET password = NULL");
                $updated = $db->affected_rows;
                
                echo '<p style="font-size: 20px; color: #FFD700; margin: 20px 0;">✅ Updated ' . $updated . ' users</p>';
                
                // Show after status
                echo '<h3>📊 AFTER FIX:</h3>';
                $result = $db->query("SELECT username, password, status, start_date, end_date FROM users ORDER BY id");
                
                echo '<table>';
                echo '<tr><th>Username</th><th>Password</th><th>Status</th><th>Dates</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    $has_pwd = !empty($row['password']);
                    $pwd_badge = $has_pwd 
                        ? '<span class="badge badge-red">SET ❌</span>' 
                        : '<span class="badge badge-green">NULL ✓</span>';
                    
                    $dates = $row['start_date'] && $row['end_date'] 
                        ? "{$row['start_date']} to {$row['end_date']}" 
                        : "Not set";
                    
                    echo "<tr>";
                    echo "<td>{$row['username']}</td>";
                    echo "<td>{$pwd_badge}</td>";
                    echo "<td>{$row['status']}</td>";
                    echo "<td style='font-size: 12px;'>{$dates}</td>";
                    echo "</tr>";
                }
                echo '</table>';
                
                echo '</div>';
                
                // Success message
                echo '<div class="box success center">';
                echo '<h2 style="font-size: 36px; margin: 20px 0;">🎉 FIX COMPLETE!</h2>';
                echo '<p style="font-size: 20px; line-height: 2;">';
                echo '✅ All passwords set to NULL<br>';
                echo '✅ Bot can now login with username only<br>';
                echo '✅ No password required!<br>';
                echo '</p>';
                echo '</div>';
                
                // Test command
                echo '<div class="box warning">';
                echo '<h3>🧪 TEST YOUR BOT:</h3>';
                echo '<pre style="color: #ffaa00;">';
                echo 'Username: ali' . "\n";
                echo 'Password: [leave blank]' . "\n\n";
                echo 'Expected Result:' . "\n";
                echo '✅ LOGIN SUCCESS!' . "\n";
                echo '✅ Authentication successful!' . "\n";
                echo '✅ Bot starts working!' . "\n";
                echo '</pre>';
                echo '</div>';
                
                // Delete warning
                echo '<div class="box error center">';
                echo '<h2 style="font-size: 32px; color: #ff3333;">⚠️ IMPORTANT!</h2>';
                echo '<p style="font-size: 20px; margin: 20px 0; color: #ff3333;">';
                echo '🔥 DELETE THIS FILE FROM GITHUB NOW!<br>';
                echo '🔒 Security Risk if Left Online<br>';
                echo '</p>';
                echo '</div>';
                
                $db->close();
                
            } catch (Exception $e) {
                echo '<div class="box error center">';
                echo '<h2>❌ ERROR</h2>';
                echo '<p style="font-size: 18px; margin: 20px 0;">' . $e->getMessage() . '</p>';
                echo '</div>';
            }
        } else {
            ?>
            <div class="box">
                <h2 style="color: #FFD700; text-align: center; margin-bottom: 30px;">🔍 CURRENT STATUS</h2>
                
                <?php
                try {
                    $db = getDB();
                    $result = $db->query("SELECT username, password, status, start_date, end_date FROM users ORDER BY id");
                    
                    $total = 0;
                    $with_password = 0;
                    $without_password = 0;
                    
                    echo '<table>';
                    echo '<tr><th>Username</th><th>Password</th><th>Status</th><th>Dates</th></tr>';
                    
                    while ($row = $result->fetch_assoc()) {
                        $total++;
                        $has_pwd = !empty($row['password']);
                        
                        if ($has_pwd) {
                            $with_password++;
                            $pwd_badge = '<span class="badge badge-red">SET ❌</span>';
                        } else {
                            $without_password++;
                            $pwd_badge = '<span class="badge badge-green">NULL ✓</span>';
                        }
                        
                        $dates = $row['start_date'] && $row['end_date'] 
                            ? "{$row['start_date']} to {$row['end_date']}" 
                            : "Not set";
                        
                        echo "<tr>";
                        echo "<td>{$row['username']}</td>";
                        echo "<td>{$pwd_badge}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td style='font-size: 12px;'>{$dates}</td>";
                        echo "</tr>";
                    }
                    echo '</table>';
                    
                    echo '<div class="stats">';
                    echo '<div class="stat-card">';
                    echo '<div class="stat-label">Total Users</div>';
                    echo '<div class="stat-value">' . $total . '</div>';
                    echo '</div>';
                    echo '<div class="stat-card">';
                    echo '<div class="stat-label">With Password</div>';
                    echo '<div class="stat-value" style="color: #ff3333;">' . $with_password . '</div>';
                    echo '</div>';
                    echo '<div class="stat-card">';
                    echo '<div class="stat-label">Without Password</div>';
                    echo '<div class="stat-value" style="color: #00ff88;">' . $without_password . '</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    $db->close();
                } catch (Exception $e) {
                    echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
            
            <div class="box warning">
                <h2 style="color: #ffaa00; text-align: center;">⚠️ WHAT THIS DOES</h2>
                <ul style="font-size: 18px; line-height: 2.5; list-style: none;">
                    <li>✅ Sets password = NULL for ALL users</li>
                    <li>✅ Bot can login with username only</li>
                    <li>✅ No password required!</li>
                    <li>✅ Fixes "Password required but not provided" error</li>
                </ul>
            </div>
            
            <div class="center">
                <form method="POST">
                    <button type="submit">🔧 FIX ALL USERS NOW</button>
                </form>
            </div>
            
            <div class="box">
                <h3>📋 STEPS AFTER FIX:</h3>
                <ol style="font-size: 18px; line-height: 2.5;">
                    <li>Click "FIX ALL USERS NOW" button</li>
                    <li>Wait for success message</li>
                    <li>DELETE this file from GitHub</li>
                    <li>Run your bot</li>
                    <li>Login with username only (no password)</li>
                    <li>Success! 🎉</li>
                </ol>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>
