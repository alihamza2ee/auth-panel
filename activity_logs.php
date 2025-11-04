<?php
require_once 'advanced_config.php';
if (!isLoggedIn()) { header('Location: advanced_index.php'); exit(); }

$db = getDB();
$logs = $db->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Activity Logs</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui;
            background: linear-gradient(135deg, #0a0e1a 0%, #1a1f2e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #FFD700; margin-bottom: 20px; }
        .panel {
            background: rgba(255,255,255,0.05);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
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
        }
        td {
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        tr:hover { background: rgba(255,255,255,0.05); }
        .btn-back {
            background: #666;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="advanced_index.php" class="btn-back">← Back</a>
        <h1>📊 Activity Logs</h1>
        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Username</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['created_at']; ?></td>
                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                        <td><?php echo $log['ip_address']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
