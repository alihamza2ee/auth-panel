<?php
/**
 * Advanced API Endpoint
 * =====================
 * Professional bot authentication with anti-crack protection
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'advanced_config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

$action = $data['action'] ?? '';

// ============== VERSION CHECK ==============
if ($action === 'check_version') {
    $current_version = $data['current_version'] ?? '0.0.0';
    
    $db = getDB();
    $stmt = $db->query("SELECT * FROM bot_versions WHERE required = 1 ORDER BY id DESC LIMIT 1");
    $latest = $stmt->fetch_assoc();
    
    $force_update = FORCE_UPDATE || ($latest && $latest['force_update'] == 1);
    $needs_update = version_compare($current_version, REQUIRED_BOT_VERSION, '<');
    
    echo json_encode([
        'success' => true,
        'current_version' => $current_version,
        'latest_version' => REQUIRED_BOT_VERSION,
        'needs_update' => $needs_update,
        'force_update' => $force_update,
        'update_available' => $needs_update,
        'download_url' => UPDATE_URL,
        'message' => $force_update ? 'Update required to continue' : 'Update available'
    ]);
    exit();
}

// ============== LOGIN AUTHENTICATION ==============
if ($action === 'login') {
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');
    $device_id = trim($data['device_id'] ?? '');
    $hardware_id = trim($data['hardware_id'] ?? '');
    $bot_version = trim($data['version'] ?? '0.0.0');
    $client_ip = $_SERVER['REMOTE_ADDR'];
    
    // Validate inputs
    if (empty($username)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'Username required'
        ]);
        exit();
    }
    
    // Check version first
    if (FORCE_UPDATE && version_compare($bot_version, REQUIRED_BOT_VERSION, '<')) {
        http_response_code(426);
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'force_update' => true,
            'required_version' => REQUIRED_BOT_VERSION,
            'current_version' => $bot_version,
            'download_url' => UPDATE_URL,
            'message' => 'Bot update required. Please download latest version.'
        ]);
        
        logActivity('version_check_failed', "Version: $bot_version, Required: " . REQUIRED_BOT_VERSION, $username);
        exit();
    }
    
    $db = getDB();
    
    // Get user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        // Log failed attempt
        $stmt = $db->prepare("INSERT INTO login_logs (username, device_id, ip_address, status) VALUES (?, ?, ?, 'invalid')");
        $stmt->bind_param("sss", $username, $device_id, $client_ip);
        $stmt->execute();
        $stmt->close();
        
        logActivity('login_failed', 'Invalid username', $username);
        
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'Invalid access key'
        ]);
        exit();
    }
    
    // Check password ONLY if password is set in database
    // Password is now OPTIONAL - bot can authenticate with just username
    if (!empty($user['password'])) {
        // Password is set in database, check if provided
        if (empty($password)) {
            // Password required but not provided
            logActivity('login_failed', 'Password required but not provided', $username);
            
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'password_required' => true,
                'message' => 'Password required for this account'
            ]);
            exit();
        }
        
        // Password provided, check if correct
        if ($password !== $user['password']) {
            // Increment login attempts
            $stmt = $db->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
            
            logActivity('login_failed', 'Wrong password', $username);
            
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'message' => 'Invalid password'
            ]);
            exit();
        }
    }
    // If password is NULL in database, skip password check completely
    
    // Check if temp banned (too many attempts)
    if ($user['temp_banned'] == 1 || $user['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $stmt = $db->prepare("UPDATE users SET temp_banned = 1 WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
        
        logActivity('login_blocked', 'Too many failed attempts', $username);
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'temp_banned' => true,
            'message' => 'Account temporarily blocked due to multiple failed attempts. Contact admin.'
        ]);
        exit();
    }
    
    // Check if banned
    if ($user['status'] === 'banned') {
        $stmt = $db->prepare("INSERT INTO login_logs (username, device_id, ip_address, status) VALUES (?, ?, ?, 'banned')");
        $stmt->bind_param("sss", $username, $device_id, $client_ip);
        $stmt->execute();
        $stmt->close();
        
        logActivity('login_blocked', 'Account banned', $username);
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'banned' => true,
            'status' => 'banned',
            'message' => 'Your account has been banned. Contact administrator.'
        ]);
        exit();
    }
    
    // Check time-based activation
    if ($user['start_date'] && $user['end_date']) {
        $time_check = isKeyTimeValid($user['start_date'], $user['end_date']);
        
        if (!$time_check['valid']) {
            logActivity('time_validation_failed', $time_check['reason'], $username);
            
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'status' => $time_check['reason'],
                'message' => $time_check['message'],
                'start_date' => $user['start_date'],
                'end_date' => $user['end_date']
            ]);
            exit();
        }
    }
    
    // Check old expire_date for backward compatibility
    if ($user['expire_date']) {
        $expire_ts = strtotime($user['expire_date'] . ' 23:59:59');
        if (time() > $expire_ts) {
            logActivity('key_expired', 'Expire date passed', $username);
            
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'status' => 'expired',
                'message' => 'Key has expired',
                'expire_date' => $user['expire_date']
            ]);
            exit();
        }
    }
    
    // ============== HARDWARE ID BINDING ==============
    if (ENABLE_HARDWARE_LOCK) {
        $hw_fingerprint = getHardwareFingerprint($device_id, ['hw' => $hardware_id]);
        
        if (empty($user['hardware_id'])) {
            // First login - bind hardware
            $stmt = $db->prepare("UPDATE users SET hardware_id = ?, device_id = ?, last_login = NOW(), login_attempts = 0 WHERE username = ?");
            $stmt->bind_param("sss", $hw_fingerprint, $device_id, $username);
            $stmt->execute();
            $stmt->close();
            
            // Record binding
            $stmt = $db->prepare("INSERT INTO hardware_bindings (username, hardware_id, device_id, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hw_fingerprint, $device_id, $client_ip);
            $stmt->execute();
            $stmt->close();
            
            logActivity('hardware_bound', "First login from device: $device_id", $username);
            
        } elseif ($user['hardware_id'] !== $hw_fingerprint) {
            // Hardware mismatch - BLOCKED!
            logActivity('hardware_mismatch', "Attempted from different device", $username);
            
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'hardware_locked' => true,
                'message' => '🔒 This key is locked to another device! Each key can only be used on ONE PC. Contact administrator to unbind.'
            ]);
            exit();
            
        } else {
            // Same hardware - update last login
            $stmt = $db->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
            
            // Update hardware binding
            $stmt = $db->prepare("UPDATE hardware_bindings SET last_seen = NOW(), ip_address = ? WHERE username = ? AND hardware_id = ?");
            $stmt->bind_param("sss", $client_ip, $username, $hw_fingerprint);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Hardware lock disabled - just update login
        $stmt = $db->prepare("UPDATE users SET device_id = ?, last_login = NOW(), login_attempts = 0 WHERE username = ?");
        $stmt->bind_param("ss", $device_id, $username);
        $stmt->execute();
        $stmt->close();
    }
    
    // Log successful login
    $stmt = $db->prepare("INSERT INTO login_logs (username, device_id, ip_address, status) VALUES (?, ?, ?, 'success')");
    $stmt->bind_param("sss", $username, $device_id, $client_ip);
    $stmt->execute();
    $stmt->close();
    
    logActivity('login_success', "IP: $client_ip", $username);
    
    // SUCCESS RESPONSE
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'status' => 'success',
        'user_id' => $user['user_id'],
        'userid' => $user['user_id'],
        'username' => $username,
        'expire_date' => $user['expire_date'],
        'ExpireDate' => $user['expire_date'],
        'start_date' => $user['start_date'],
        'end_date' => $user['end_date'],
        'permissions' => explode(',', $user['permissions'] ?: 'basic'),
        'hardware_locked' => ENABLE_HARDWARE_LOCK,
        'message' => 'Authentication successful'
    ]);
    exit();
}

// Invalid action
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);
?>
