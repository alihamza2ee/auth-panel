<?php
/**
 * Bulk Key Generator
 * ==================
 */

require_once 'advanced_config.php';

if (!isLoggedIn()) {
    header('Location: advanced_index.php');
    exit();
}

$db = getDB();

// Handle bulk generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_bulk'])) {
    $prefix = trim($_POST['prefix']);
    $start_num = intval($_POST['start_range']);
    $end_num = intval($_POST['end_range']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $auto_password = isset($_POST['auto_password']);
    $custom_password = trim($_POST['custom_password']);
    
    $generated = 0;
    $failed = 0;
    $keys_list = [];
    
    $db->begin_transaction();
    
    try {
        for ($i = $start_num; $i <= $end_num; $i++) {
            $username = generateUsername($prefix, $i);
            $user_id = generateUserId();
            $password = $auto_password ? generatePassword() : $custom_password;
            
            $stmt = $db->prepare("INSERT INTO users (username, user_id, password, status, start_date, end_date) VALUES (?, ?, ?, 'active', ?, ?)");
            $stmt->bind_param("sssss", $username, $user_id, $password, $start_date, $end_date);
            
            if ($stmt->execute()) {
                $generated++;
                $keys_list[] = [
                    'username' => $username,
                    'password' => $password,
                    'user_id' => $user_id
                ];
            } else {
                $failed++;
            }
            $stmt->close();
        }
        
        $db->commit();
        
        $_SESSION['bulk_result'] = [
            'success' => true,
            'generated' => $generated,
            'failed' => $failed,
            'keys' => $keys_list,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['bulk_result'] = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    header('Location: bulk_generator.php?result=1');
    exit();
}

// Get result
$result = null;
if (isset($_GET['result']) && isset($_SESSION['bulk_result'])) {
    $result = $_SESSION['bulk_result'];
    unset($_SESSION['bulk_result']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bulk Key Generator</title>
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
        .header {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { color: #FFD700; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back { background: #666; color: #fff; }
        .form-container {
            background: rgba(255,255,255,0.05);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #00ff88;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: #fff;
        }
        .btn-generate {
            background: linear-gradient(135deg, #00ff88, #00cc66);
            color: #000;
            width: 100%;
            padding: 15px;
            font-size: 1.1em;
        }
        .result-box {
            background: rgba(0,255,136,0.1);
            border: 2px solid #00ff88;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .error-box {
            background: rgba(255,0,0,0.1);
            border: 2px solid #ff0000;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .keys-table {
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: rgba(0,255,136,0.2);
            color: #00ff88;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .btn-export {
            background: #0088ff;
            color: #fff;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Bulk Key Generator</h1>
            <a href="advanced_index.php" class="btn btn-back">← Back to Dashboard</a>
        </div>
        
        <?php if ($result): ?>
            <?php if ($result['success']): ?>
                <div class="result-box">
                    <h2>✅ Success!</h2>
                    <p><strong>Generated:</strong> <?php echo $result['generated']; ?> keys</p>
                    <?php if ($result['failed'] > 0): ?>
                        <p><strong>Failed:</strong> <?php echo $result['failed']; ?> keys</p>
                    <?php endif; ?>
                    <p><strong>Valid From:</strong> <?php echo $result['start_date']; ?></p>
                    <p><strong>Valid Until:</strong> <?php echo $result['end_date']; ?></p>
                    
                    <div class="keys-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>User ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['keys'] as $key): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($key['username']); ?></td>
                                    <td><?php echo htmlspecialchars($key['password']); ?></td>
                                    <td><?php echo htmlspecialchars($key['user_id']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <button onclick="exportKeys()" class="btn btn-export">💾 Export to TXT</button>
                </div>
            <?php else: ?>
                <div class="error-box">
                    <h2>❌ Error</h2>
                    <p><?php echo htmlspecialchars($result['message']); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="form-container">
            <h2 style="color: #00ff88; margin-bottom: 20px;">Generate Multiple Keys</h2>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username Prefix</label>
                        <input type="text" name="prefix" value="USER_" required>
                        <small style="color: #888;">Example: USER_ will create USER_0001, USER_0002...</small>
                    </div>
                    
                    <div></div>
                    
                    <div class="form-group">
                        <label>Start Range</label>
                        <input type="number" name="start_range" value="1" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Range</label>
                        <input type="number" name="end_range" value="100" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Start Date (Valid From)</label>
                        <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Date (Valid Until)</label>
                        <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="auto_password" checked onchange="togglePassword(this)">
                            Auto-generate Passwords
                        </label>
                    </div>
                    
                    <div class="form-group" id="customPasswordDiv" style="display: none;">
                        <label>Custom Password (for all keys)</label>
                        <input type="text" name="custom_password" id="customPassword">
                    </div>
                </div>
                
                <button type="submit" name="generate_bulk" class="btn btn-generate">
                    🔑 Generate Keys
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function togglePassword(checkbox) {
            document.getElementById('customPasswordDiv').style.display = 
                checkbox.checked ? 'none' : 'block';
            document.getElementById('customPassword').required = !checkbox.checked;
        }
        
        function exportKeys() {
            <?php if ($result && $result['success']): ?>
                let text = "=".repeat(60) + "\n";
                text += "BULK GENERATED KEYS\n";
                text += "Generated: <?php echo date('Y-m-d H:i:s'); ?>\n";
                text += "Valid From: <?php echo $result['start_date']; ?>\n";
                text += "Valid Until: <?php echo $result['end_date']; ?>\n";
                text += "Total Keys: <?php echo $result['generated']; ?>\n";
                text += "=".repeat(60) + "\n\n";
                
                <?php foreach ($result['keys'] as $key): ?>
                text += "Username: <?php echo $key['username']; ?>\n";
                text += "Password: <?php echo $key['password']; ?>\n";
                text += "User ID:  <?php echo $key['user_id']; ?>\n";
                text += "-".repeat(60) + "\n";
                <?php endforeach; ?>
                
                const blob = new Blob([text], { type: 'text/plain' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'keys_' + Date.now() + '.txt';
                a.click();
            <?php endif; ?>
        }
    </script>
</body>
</html>
