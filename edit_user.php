<?php
require_once 'advanced_config.php';
if (!isLoggedIn()) { header('Location: advanced_index.php'); exit(); }

$username = $_GET['user'] ?? '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $unbind = isset($_POST['unbind_hardware']);
    
    if ($unbind) {
        $stmt = $db->prepare("UPDATE users SET status = ?, start_date = ?, end_date = ?, hardware_id = NULL, device_id = NULL WHERE username = ?");
    } else {
        $stmt = $db->prepare("UPDATE users SET status = ?, start_date = ?, end_date = ? WHERE username = ?");
    }
    $stmt->bind_param("ssss", $status, $start_date, $end_date, $username);
    $stmt->execute();
    
    logActivity('user_updated', "Updated: $username", ADMIN_USER);
    header('Location: advanced_index.php?success=User updated');
    exit();
}

$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui;
            background: linear-gradient(135deg, #0a0e1a 0%, #1a1f2e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 600px; margin: 50px auto; }
        .panel {
            background: rgba(255,255,255,0.05);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        h1 { color: #FFD700; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #00ff88; }
        input, select {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: #fff;
        }
        .btn {
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
        }
        .btn-primary { background: linear-gradient(135deg, #00ff88, #00cc66); color: #000; }
        .btn-secondary { background: #666; color: #fff; text-decoration: none; display: inline-block; }
        .info-box {
            background: rgba(255,170,0,0.1);
            border: 1px solid #ffaa00;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel">
            <h1>✏️ Edit User</h1>
            
            <?php if ($user['hardware_id']): ?>
                <div class="info-box">
                    🔒 <strong>Hardware Locked</strong><br>
                    This key is bound to a specific device.
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="banned" <?php echo $user['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $user['start_date']; ?>">
                </div>
                
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo $user['end_date']; ?>">
                </div>
                
                <?php if ($user['hardware_id']): ?>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="unbind_hardware" style="width: auto;">
                            <span>Unbind Hardware (Allow new device)</span>
                        </label>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                <a href="advanced_index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
