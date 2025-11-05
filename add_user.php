<?php
require_once 'advanced_config.php';
if (!isLoggedIn()) { header('Location: advanced_index.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO users (username, password, status, start_date, end_date) VALUES (?, ?, 'active', ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $start_date, $end_date);
    
    if ($stmt->execute()) {
        logActivity('user_created', "Created user: $username", ADMIN_USER);
        header('Location: advanced_index.php?success=User created');
    } else {
        $error = "Failed to create user";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
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
        input {
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
        .btn-secondary { background: #666; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel">
            <h1>➕ Add New User</h1>
            <?php if (isset($error)): ?>
                <div style="background: rgba(255,0,0,0.2); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="text" name="password" value="<?php echo generatePassword(); ?>" required>
                </div>
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>End Date *</label>
                    <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="advanced_index.php" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
