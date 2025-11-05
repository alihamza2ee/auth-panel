<?php
/**
 * ADVANCED API - READY TO UPLOAD
 * ===============================
 * USERNAME ONLY - NO PASSWORD CHECK
 * Change database settings below (lines 22-26)
 */

ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// DATABASE SETTINGS - CHANGE THESE!
// ============================================
define('DB_HOST', 'autorack.proxy.rlwy.net');
define('DB_PORT', '12345');  // ← CHANGE THIS
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_PASSWORD_HERE');  // ← CHANGE THIS
define('DB_NAME', 'railway');

define('ENABLE_HARDWARE_LOCK', true);

// ============================================
// DATABASE CONNECTION
// ============================================
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            if ($db->connect_error) {
                throw new Exception('DB connection failed');
            }
            $db->set_charset('utf8mb4');
        } catch (Exception $e) {
            echo json_encode(['error' => 'Database error']);
            exit();
        }
    }
    return $db;
}

function logActivity($action, $details, $username = '') {
    try {
        $db = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = $db->prepare("INSERT INTO activity_logs (username, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $action, $details, $ip);
            $stmt->execute();
        }
    } catch (Exception $e) {
        // Silent fail
    }
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit();
}

$action = $data['action'] ?? '';

// ============================================
// LOGIN - USERNAME ONLY
// ============================================
if ($action === 'login') {
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';  // IGNORED
    $device_id = $data['device_id'] ?? '';
    $hardware_id = $data['hardware_id'] ?? '';
    $version = $data['version'] ?? '';
    
    logActivity('login_attempt', "User: $username", $username);
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'authenticated' => false, 'message' => 'Username required']);
        exit();
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            echo json_encode(['success' => false, 'authenticated' => false, 'message' => 'User not found']);
            exit();
        }
        
        if ($user['status'] === 'banned') {
            echo json_encode(['success' => false, 'authenticated' => false, 'banned' => true, 'status' => 'banned', 'message' => 'Account is banned']);
            exit();
        }
        
        $current_date = date('Y-m-d');
        $start_date = $user['start_date'];
        $end_date = $user['end_date'];
        
        if ($current_date < $start_date) {
            echo json_encode(['success' => false, 'authenticated' => false, 'status' => 'not_started', 'message' => 'Key activation date not reached']);
            exit();
        }
        
        if ($current_date > $end_date) {
            echo json_encode(['success' => false, 'authenticated' => false, 'status' => 'expired', 'message' => 'Key has expired']);
            exit();
        }
        
        if (ENABLE_HARDWARE_LOCK && !empty($user['hardware_id'])) {
            if ($user['hardware_id'] !== $hardware_id) {
                echo json_encode(['success' => false, 'authenticated' => false, 'hardware_locked' => true, 'message' => 'Key is locked to another device']);
                exit();
            }
        }
        
        if (ENABLE_HARDWARE_LOCK && empty($user['hardware_id']) && !empty($hardware_id)) {
            $stmt = $db->prepare("UPDATE users SET hardware_id = ?, device_id = ? WHERE username = ?");
            $stmt->bind_param("sss", $hardware_id, $device_id, $username);
            $stmt->execute();
        }
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = $db->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE username = ?");
        $stmt->bind_param("ss", $ip_address, $username);
        $stmt->execute();
        
        logActivity('login_success', "IP: $ip_address", $username);
        
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user_id' => $user['id'],
            'userid' => $user['id'],
            'username' => $user['username'],
            'start_date' => $user['start_date'],
            'end_date' => $user['end_date'],
            'ExpireDate' => $user['end_date'],
            'status' => 'active',
            'hardware_locked' => !empty($user['hardware_id']),
            'permissions' => ['basic'],
            'message' => 'Authentication successful'
        ]);
        exit();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error']);
        exit();
    }
}

// ============================================
// VERSION CHECK
// ============================================
if ($action === 'check_version') {
    $current_version = $data['current_version'] ?? '0.0.0';
    
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM bot_versions ORDER BY id DESC LIMIT 1");
        $latest = $stmt->fetch_assoc();
        
        if (!$latest) {
            echo json_encode(['force_update' => false, 'needs_update' => false, 'latest_version' => $current_version, 'current_version' => $current_version]);
            exit();
        }
        
        $required_version = $latest['version'];
        $force_update = (bool)$latest['force_update'];
        $needs_update = version_compare($current_version, $required_version, '<');
        
        if ($needs_update && $force_update) {
            echo json_encode(['force_update' => true, 'update_required' => true, 'needs_update' => true, 'required_version' => $required_version, 'current_version' => $current_version, 'download_url' => $latest['download_url'] ?? '', 'message' => 'Update required']);
        } else if ($needs_update) {
            echo json_encode(['force_update' => false, 'needs_update' => true, 'latest_version' => $required_version, 'current_version' => $current_version, 'download_url' => $latest['download_url'] ?? '', 'message' => 'Update available']);
        } else {
            echo json_encode(['force_update' => false, 'needs_update' => false, 'latest_version' => $required_version, 'current_version' => $current_version, 'message' => 'Version up to date']);
        }
        exit();
    } catch (Exception $e) {
        echo json_encode(['force_update' => false, 'needs_update' => false, 'message' => 'Version check unavailable']);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
ob_end_clean();
?>
