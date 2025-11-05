<?php
/**
 * ADVANCED ADMIN PANEL - MAIN PAGE
 * Complete dashboard with all features
 */

session_start();

// Database connection
$db_host = 'mysql.railway.internal';
$db_port = '3306';
$db_user = 'root';
$db_pass = 'iDFjnbMKzOTFBuwlZjZgzKiEBBAJDBmD';
$db_name = 'railway';

try {
    $db = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    $db->set_charset('utf8mb4');
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Get stats
$total_users = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$active_users = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'] ?? 0;
$banned_users = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'banned'")->fetch_assoc()['count'] ?? 0;
$expired_users = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'expired' OR end_date < CURDATE()")->fetch_assoc()['count'] ?? 0;

// Check if login_logs table exists
$table_check = $db->query("SHOW TABLES LIKE 'login_logs'");
if ($table_check && $table_check->num_rows > 0) {
    $total_logins = $db->query("SELECT COUNT(*) as count FROM login_logs")->fetch_assoc()['count'] ?? 0;
} else {
    $total_logins = 0;
}

// Get recent users
$recent_users = $db->query("SELECT username, status, start_date, end_date, last_login, hardware_id FROM users ORDER BY created_at DESC LIMIT 10");

// Get bot version
$version_result = $db->query("SELECT version, force_update FROM bot_versions ORDER BY id DESC LIMIT 1");
$bot_version = $version_result ? $version_result->fetch_assoc() : ['version' => '5.0.0', 'force_update' => 0];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 42px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-card.active .number { color: #10b981; }
        .stat-card.banned .number { color: #ef4444; }
        .stat-card.expired .number { color: #f59e0b; }
        
        .panel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .panel h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background: #f8f9fa;
            color: #667eea;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge.banned {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge.expired {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge.locked {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge.free {
            background: #d1fae5;
            color: #065f46;
        }
        
        .version-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .version-info h3 {
            margin-bottom: 10px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 14px;
            }
            
            table th,
            table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎮 Bot Admin Panel</h1>
            <p>Manage users, monitor activity, and control bot settings</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $total_users; ?></div>
            </div>
            
            <div class="stat-card active">
                <h3>Active Users</h3>
                <div class="number"><?php echo $active_users; ?></div>
            </div>
            
            <div class="stat-card banned">
                <h3>Banned Users</h3>
                <div class="number"><?php echo $banned_users; ?></div>
            </div>
            
            <div class="stat-card expired">
                <h3>Expired Users</h3>
                <div class="number"><?php echo $expired_users; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Logins</h3>
                <div class="number"><?php echo $total_logins; ?></div>
            </div>
        </div>
        
        <div class="panel">
            <div class="version-info">
                <h3>🔧 Bot Version</h3>
                <p style="font-size: 24px; font-weight: bold;">v<?php echo htmlspecialchars($bot_version['version']); ?></p>
                <p>Force Update: <?php echo $bot_version['force_update'] ? '✅ Enabled' : '❌ Disabled'; ?></p>
            </div>
            
            <div class="actions">
                <a href="add_user.php" class="btn btn-success">➕ Add New User</a>
                <a href="manage_users.php" class="btn btn-primary">👥 Manage Users</a>
                <a href="settings.php" class="btn btn-warning">⚙️ Settings</a>
                <a href="logs.php" class="btn btn-primary">📊 View Logs</a>
            </div>
        </div>
        
        <div class="panel">
            <h2>📋 Recent Users</h2>
            
            <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Last Login</th>
                            <th>Hardware</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td>
                                    <?php
                                    $status = $user['status'];
                                    if ($user['end_date'] < date('Y-m-d')) {
                                        $status = 'expired';
                                    }
                                    echo "<span class='badge $status'>" . ucfirst($status) . "</span>";
                                    ?>
                                </td>
                                <td><?php echo $user['start_date']; ?></td>
                                <td><?php echo $user['end_date']; ?></td>
                                <td><?php echo $user['last_login'] ?: 'Never'; ?></td>
                                <td>
                                    <?php if ($user['hardware_id']): ?>
                                        <span class="badge locked">🔒 Locked</span>
                                    <?php else: ?>
                                        <span class="badge free">🔓 Free</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <h3>No users yet</h3>
                    <p>Add your first user to get started</p>
                    <br>
                    <a href="add_user.php" class="btn btn-success">➕ Add User Now</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="panel">
            <h2>🚀 Quick Actions</h2>
            <div class="actions">
                <button class="btn btn-primary" onclick="window.location.href='unbind_all.php'">🔓 Unbind All Hardware</button>
                <button class="btn btn-warning" onclick="window.location.href='expire_check.php'">🔄 Check Expired Users</button>
                <button class="btn btn-danger" onclick="if(confirm('Delete all expired users?')) window.location.href='cleanup.php'">🗑️ Cleanup Expired</button>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$db->close();
?>
