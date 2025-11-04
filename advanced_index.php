<?php
/**
 * Advanced Dashboard
 * ==================
 * Professional admin panel with all features
 */

require_once 'advanced_config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: advanced_index.php');
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        logActivity('admin_login', 'Admin logged in', ADMIN_USER);
        header('Location: advanced_index.php');
        exit();
    } else {
        $error = "Invalid credentials!";
        logActivity('admin_login_failed', 'Failed login attempt', $username);
    }
}

// Check authentication
if (!isLoggedIn()) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Advanced Auth Panel</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, system-ui;
                background: linear-gradient(135deg, #0a0e1a 0%, #1a1f2e 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-box {
                background: rgba(255,255,255,0.05);
                padding: 50px;
                border-radius: 20px;
                border: 1px solid rgba(255,255,255,0.1);
                backdrop-filter: blur(10px);
                width: 400px;
            }
            h1 {
                text-align: center;
                background: linear-gradient(135deg, #FFD700, #FFA500);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                font-size: 2.5em;
                margin-bottom: 40px;
            }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 8px; color: #fff; }
            input {
                width: 100%;
                padding: 15px;
                background: rgba(0,0,0,0.3);
                border: 1px solid rgba(255,255,255,0.2);
                border-radius: 10px;
                color: #fff;
                font-size: 1em;
            }
            input:focus { outline: none; border-color: #00ff88; }
            button {
                width: 100%;
                padding: 15px;
                background: linear-gradient(135deg, #00ff88, #00cc66);
                border: none;
                border-radius: 10px;
                color: #000;
                font-size: 1.1em;
                font-weight: bold;
                cursor: pointer;
            }
            .error {
                background: rgba(255,0,0,0.2);
                color: #ff6666;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                border: 1px solid #ff0000;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>🔐</h1>
            <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Dashboard
$db = getDB();

// Get stats
$total_users = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$active_users = $db->query("SELECT COUNT(*) as c FROM users WHERE status = 'active'")->fetch_assoc()['c'];
$banned_users = $db->query("SELECT COUNT(*) as c FROM users WHERE status = 'banned'")->fetch_assoc()['c'];
$total_logins = $db->query("SELECT COUNT(*) as c FROM login_logs")->fetch_assoc()['c'];
$hardware_locked = $db->query("SELECT COUNT(*) as c FROM users WHERE hardware_id IS NOT NULL")->fetch_assoc()['c'];

// Get recent users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);

// Get recent activity
$activities = $db->query("SELECT * FROM activity_logs ORDER BY timestamp DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Authentication Panel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, system-ui;
            background: linear-gradient(135deg, #0a0e1a 0%, #1a1f2e 100%);
            color: #fff;
            min-height: 100vh;
        }
        .container { max-width: 1600px; margin: 0 auto; padding: 20px; }
        .header {
            background: linear-gradient(135deg, #2d2f3e 0%, #1a1f2e 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        .header h1 {
            font-size: 2.5em;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ff0000;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .stat-card h3 { color: #00ff88; font-size: 0.9em; margin-bottom: 10px; }
        .stat-card .number { font-size: 2em; font-weight: bold; color: #FFD700; }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .action-card {
            background: linear-gradient(135deg, #2d2f3e 0%, #1a1f2e 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .action-card:hover { transform: translateY(-5px); }
        .action-card .icon { font-size: 3em; margin-bottom: 10px; }
        .action-card h3 { color: #00ff88; margin-bottom: 5px; }
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .panel {
            background: rgba(255,255,255,0.05);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .panel h2 {
            color: #00ff88;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(0,255,136,0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: rgba(0,255,136,0.1);
            color: #00ff88;
            padding: 12px;
            text-align: left;
            font-size: 0.9em;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.85em;
        }
        tr:hover { background: rgba(255,255,255,0.05); }
        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-active { background: rgba(0,255,136,0.2); color: #00ff88; border: 1px solid #00ff88; }
        .status-banned { background: rgba(255,0,0,0.2); color: #ff0000; border: 1px solid #ff0000; }
        .activity-item {
            padding: 10px;
            margin-bottom: 10px;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            border-left: 3px solid #00ff88;
        }
        .activity-time { color: #888; font-size: 0.85em; }
        .search-bar {
            margin-bottom: 15px;
        }
        .search-bar input {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: #fff;
        }
        @media (max-width: 1200px) {
            .main-content { grid-template-columns: 1fr; }
            .stats { grid-template-columns: repeat(3, 1fr); }
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="?logout" class="logout-btn">🚪 Logout</a>
            <h1>🔐 Advanced Authentication Panel</h1>
            <p style="color: #aaa;">Professional Key Management System</p>
            
            <div class="stats">
                <div class="stat-card">
                    <h3>TOTAL USERS</h3>
                    <div class="number"><?php echo $total_users; ?></div>
                </div>
                <div class="stat-card">
                    <h3>ACTIVE KEYS</h3>
                    <div class="number"><?php echo $active_users; ?></div>
                </div>
                <div class="stat-card">
                    <h3>BANNED KEYS</h3>
                    <div class="number"><?php echo $banned_users; ?></div>
                </div>
                <div class="stat-card">
                    <h3>TOTAL LOGINS</h3>
                    <div class="number"><?php echo $total_logins; ?></div>
                </div>
                <div class="stat-card">
                    <h3>HW LOCKED</h3>
                    <div class="number"><?php echo $hardware_locked; ?></div>
                </div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="add_user.php" class="action-card">
                <div class="icon">➕</div>
                <h3>Add User</h3>
                <p style="color: #888;">Create single key</p>
            </a>
            <a href="bulk_generator.php" class="action-card">
                <div class="icon">🔑</div>
                <h3>Bulk Generator</h3>
                <p style="color: #888;">Generate multiple keys</p>
            </a>
            <a href="settings.php" class="action-card">
                <div class="icon">⚙️</div>
                <h3>Settings</h3>
                <p style="color: #888;">Force update, version</p>
            </a>
            <a href="activity_logs.php" class="action-card">
                <div class="icon">📊</div>
                <h3>Activity Logs</h3>
                <p style="color: #888;">View full history</p>
            </a>
        </div>
        
        <div class="main-content">
            <div class="panel">
                <h2>📋 Recent Users</h2>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="🔍 Search users..." onkeyup="searchTable()">
                </div>
                <div style="overflow-x: auto;">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Valid Period</th>
                                <th>HW Lock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo strtoupper($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['start_date'] && $user['end_date']): ?>
                                        <?php echo $user['start_date']; ?> → <?php echo $user['end_date']; ?>
                                    <?php else: ?>
                                        <?php echo $user['expire_date'] ?: 'N/A'; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['hardware_id'] ? '🔒 Locked' : '🔓 Free'; ?></td>
                                <td>
                                    <a href="edit_user.php?user=<?php echo urlencode($user['username']); ?>" style="color: #0088ff;">Edit</a>
                                    |
                                    <a href="delete_user.php?user=<?php echo urlencode($user['username']); ?>" style="color: #ff0000;" onclick="return confirm('Delete?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="panel">
                <h2>📜 Recent Activity</h2>
                <div style="max-height: 600px; overflow-y: auto;">
                    <?php foreach ($activities as $activity): ?>
                    <div class="activity-item">
                        <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
                        <div style="color: #aaa; font-size: 0.9em;">
                            <?php echo htmlspecialchars($activity['username']); ?>
                            <?php if ($activity['details']): ?>
                                - <?php echo htmlspecialchars($activity['details']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="activity-time"><?php echo $activity['timestamp']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('usersTable');
            const tr = table.getElementsByTagName('tr');
            
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>
